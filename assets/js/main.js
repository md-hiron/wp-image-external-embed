(function($){
    $(document).ready(function(){
        $('img').on('contextmenu', function(e){
            e.preventDefault();
            const imgSrc    = $(this).attr('src');
            const imgWidth  = Number( $(this).attr('width') );
            const imgHeight = Number( $(this).attr('height') );
            const imgAlt    = $(this).attr('alt');

            const largeImgDimentions = getImageScaleDimensions( imgWidth, imgHeight, 500  );
            const smallImgDimentions = getImageScaleDimensions( imgWidth, imgHeight, 300  );

            let largeImageAttr = '';
            let smallImageAttr = '';

            if( largeImgDimentions ){
                largeImageAttr = `width="${largeImgDimentions.width}" height="${largeImgDimentions.height}"`;
            }

            if( smallImgDimentions ){
                smallImageAttr = `width="${smallImgDimentions.width}" height="${smallImgDimentions.height}"`;
            }

            //run image meta api for image meta data
            fetch_image_meta( window.location.origin + '/wp-test', imgSrc ).then( function( imageCredit ){
               
                $('#bs24-embed-large-image-input').val( getEmbedImage( imgSrc, largeImageAttr, imageCredit ) );
                $('#bs24-embed-small-image-input').val( getEmbedImage( imgSrc, smallImageAttr, imageCredit ) );

                $('#bs24-embed-popup').show();
            } );
        });

        $('.bs24-embed-image-input').on('focus', function(){
            $(this).select();
        });

        $('.bs24-embed-popup-close').on('click', function(){
            $('#bs24-embed-popup').hide();
        });

        /**
         * 
         * @param {string} imgSrc 
         * @param {string} imageAttr 
         * @param {object} imageCredit 
         * @returns string
         */
        function getEmbedImage( imgSrc, imageAttr, imageCredit ){
            const pageLink = window.location.href;
            let html = `<div><a href="${pageLink}" rel="follow" target="_blank">
                        <img src="${imgSrc}" ${imageAttr} nopin="nopin" ondragstart="return false;" onselectstart="return false;" oncontextmenu="return false;" />
                    </a></div>`;
            
            //check if image credit object is exist
            if( typeof imageCredit === 'object' && imageCredit !== null && imageCredit.credit_text !== '' ){
                html += `<div style='color:#444;'><small><a style="text-decoration:none;color:#444;" href="${imageCredit?.credit_url}" target="_blank">${imageCredit?.credit_text}</a></small></div>`;
            }

            return minifyHTML( html );
        }

        /**
         * Get Image scale dimensions
         * @param {int} orgWidth 
         * @param {int} orgHeight 
         * @param {int} expWidth 
         * @returns object
         */
        function getImageScaleDimensions( orgWidth, orgHeight, expWidth ){
            if( !orgWidth || !orgHeight || !expWidth  ){
                return false;
            }

            const expRatio = orgHeight / orgWidth;
            const expHeight = Math.round(expWidth * expRatio);

            return {
                width: expWidth,
                height: expHeight
            }
        }

        /**
         * Minify HTML
         * @param {string} html 
         * @returns string
         */
        function minifyHTML(html) {
            return html
              .replace(/\s+/g, ' ')           // Replace multiple whitespace with a single space
              .replace(/>\s+</g, '><')        // Remove spaces between tags
              .replace(/<!--[\s\S]*?-->/g, ''); // Remove comments
        }

        /**
         * Fetch Image meta API 
         * @param {string} apiEndpoint API endpoint for image meta
         * @param {string} url Attachment URL
         * @returns {Promise}
         */
        async function fetch_image_meta( apiEndpoint, url ){
            try{
                const response = await fetch( apiEndpoint + '/wp-json/bs24/v1/image-meta?url='+ url );
                const data     = await response.json();
                
                return data;
            }catch( error ){
                throw new Error( error );
            }
        }
    });
    
})(jQuery)