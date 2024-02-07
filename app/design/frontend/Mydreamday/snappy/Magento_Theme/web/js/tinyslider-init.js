(function () {
    'use strict';
    $(function () {
        if($('#slider-product-grid').length){
            tns({
                container: '#slider-product-grid',
                items: 4,
                rewind: true,
                swipeAngle: false,
                speed: 400,
                autoplay: true,
                nav: false,
                controlsContainer: "#customize-controls",
                gutter: 15,
                mouseDrag: true,
                responsive: {
                    320: {
                        items: 2,
                        controls: false
                    },
                    768: {
                        items: 3,
                        controls: false
                    },
                    1200: {
                        items: 3,
                        controls: true,
                        controlsContainer: "#customize-controls",
                    },
                    1368: {
                        items: 4,
                        controls: true,
                        controlsContainer: "#customize-controls",
                    },
                },
            })
        }
        
        if($('#slider-product-grid-related').length){
            tns({
                container: '#slider-product-grid-related',
                items: 4,
                rewind: true,
                swipeAngle: false,
                speed: 400,
                nav: false,
                autoplay: true,
                controlsContainer: "#customize-controls-related",
                gutter: 15,
                mouseDrag: true,
                responsive: {
                    320: {
                        items: 2,
                        controls: false
                    },
                    768: {
                        items: 3,
                        controls: false
                    },
                    1200: {
                        items: 3,
                        controls: true,
                        controlsContainer: "#customize-controls-related",
                    },
                    1368: {
                        items: 4,
                        controls: true,
                        controlsContainer: "#customize-controls-related",
                    },
                },
            })
        }

        if($('#slider-product-grid-upsell').length){
            tns({
                container: '#slider-product-grid-upsell',
                items: 4,
                rewind: true,
                swipeAngle: false,
                speed: 400,
                nav: false,
                autoplay: true,
                controlsContainer: "#customize-controls-upsell",
                gutter: 15,
                mouseDrag: true,
                responsive: {
                    320: {
                        items: 2,
                        controls: false
                    },
                    768: {
                        items: 3,
                        controls: false
                    },
                    1200: {
                        items: 3,
                        controls: true,
                        controlsContainer: "#customize-controls-upsell",
                    },
                    1368: {
                        items: 4,
                        controls: true,
                        controlsContainer: "#customize-controls-upsell",
                    },
                },
            })
        }
        
    })

})();
