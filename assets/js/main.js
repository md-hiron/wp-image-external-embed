(function($){
    $(document).ready(function(){
        $('img').on('contextmenu', function(e){
            e.preventDefault();
            const imgSrc   = $(this).attr('src');
            const imgWidth = Number( $(this).attr('width') );
            const imgHeight = Number( $(this).attr('height') );
            const imgAlt = $(this).attr('alt');
            

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

            $('#bs24-embed-large-image-input').val( getEmbedImage( imgSrc, largeImageAttr ) );
            $('#bs24-embed-small-image-input').val( getEmbedImage( imgSrc, smallImageAttr ) );

            $('#bs24-embed-popup').show();
            
        });

        $('.bs24-embed-image-input').on('focus', function(){
            $(this).select();
        });

        $('.bs24-embed-popup-close').on('click', function(){
            $('#bs24-embed-popup').hide();
        });

        function getEmbedImage( imgSrc, imageAttr ){
            const pageLink = window.location.href;
            return minifyHTML(`<div><a href="${pageLink}" rel="follow" target="_blank">
                        <img src="${imgSrc}" ${imageAttr} nopin="nopin" ondragstart="return false;" onselectstart="return false;" oncontextmenu="return false;" />
                    </a></div>
                    <div style='color:#444;'><small><a style="text-decoration:none;color:#444;" href="${pageLink}" target="_blank">Photo by Badsanieren24</a> - <a style="text-decoration:none;color:#444;" href="https://www.badsanieren24.de/" target="_blank">Discover bathroom design inspiration</a></small></div>`);
        }

        function getImageScaleDimensions( orgWidth, orgHeight, expWidth ){
            if( !orgWidth || !orgHeight  ){
                return false;
            }

            const expRatio = orgHeight / orgWidth;
            const expHeight = Math.round(expWidth * expRatio);

            return {
                width: expWidth,
                height: expHeight
            }
        }

        function minifyHTML(html) {
            return html
              .replace(/\s+/g, ' ')           // Replace multiple whitespace with a single space
              .replace(/>\s+</g, '><')        // Remove spaces between tags
              .replace(/<!--[\s\S]*?-->/g, ''); // Remove comments
        }
    });
})(jQuery)