define([], function() {

    function configSlidestoShow(region, carouselClass) {
        var widthGrid = $('#block-region-'+region).width();
        var carrouselGrid = $('.'+carouselClass);
        var countCards = parseInt(carrouselGrid.attr('data-cards-number'));
        if(widthGrid < 576) {
            countCards = 1;
        }
        else {
            var screenRatio = widthGrid/1200;
            var countCardsTight = Math.round(screenRatio*countCards);
            countCards = countCardsTight;
            // console.log(countCards);
        }

        $('.'+carouselClass).slick({
            speed: 500,
            slidesToShow: countCards,
            slidesToScroll: 1,
        });
        
    };

    function updateSlidestoShow(region, carouselClass) {
        var widthGrid = $('#block-region-'+region).width();
        var carrouselGrid = $('.'+carouselClass);
        var countCards = parseInt(carrouselGrid.attr('data-cards-number'));
        if(widthGrid < 576) {
            countCards = 1;
        }
        else {
            var screenRatio = widthGrid/1200;
            var countCardsTight = Math.round(screenRatio*countCards);
            countCards = countCardsTight;
            // console.log(countCards);
        }

        $('.'+carouselClass).slick('slickSetOption', 'slidesToShow', countCards).slick('refresh');        
    };

    function updateCarousels(region) {
        var slicks = $('.slick2');
        slicks.each(function(index, value) {
            if(value.classList.length)
            {
                var classesArray = Array.from(value.classList);
                var match = classesArray.find(element => {
                    if (element.includes("carousel_")) {
                      return true;
                    }
                });
                if(match) {
                    updateSlidestoShow(region, match);
                }
                console.log(match);
            }
        });
    }

    return {
        configSlidestoShow: configSlidestoShow,
        updateCarousels: updateCarousels
    };
});