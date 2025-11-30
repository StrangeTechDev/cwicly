function sliderMake(clientId, SliderLoop, sliderButtons, sliderAlign, sliderDots, sliderArrowShape) {
    var mySlider = document.querySelector(`#block-${clientId} > .block-editor-block-list__layout`);
    window['slider-' + clientId] = new Flickity(mySlider, {
        draggable: false,
        wrapAround: SliderLoop,
        setGallerySize: false,
        freeScroll: false,
        autoPlay: false,
        pauseAutoPlayOnHover: false,
        cellSelector: '.cc-slider',
        prevNextButtons: sliderButtons,
        cellAlign: sliderAlign,

        pageDots: sliderDots,
        arrowShape: sliderArrowShape === 'arrow1' ? 'M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z' : sliderArrowShape === 'arrow2' ? 'M 15,50 L 60,100 L 70,100 L 40,50  L 70,0 L 60,0 Z' : sliderArrowShape === 'arrow3' ? 'M 0,50 L 60,00 L 50,30 L 80,30 L 80,70 L 50,70 L 60,100 Z' :
            sliderArrowShape === 'arrow4' ? 'M 0,50 L 60,85 L 80,90 L 40,50  L 80,10 L 60,15 Z' : sliderArrowShape === 'arrow4' ? 'M 10,50 L 60,100 L 65,95 L 20,50  L 65,5 L 60,0 Z' : sliderArrowShape === 'arrow6' ? 'M92.5,42.5H25.6L37.8,30.3A7.5,7.5,0,1,0,27.2,19.7l-25,25a7.5,7.5,0,0,0,0,10.6l25,25a7.5,7.5,0,0,0,10.6,0,7.5,7.5,0,0,0,0-10.6L25.6,57.5H92.5a7.5,7.5,0,0,0,0-15Z' : ''

    });
}

function ccGalleryMake(clientId) {
    var grid = document.querySelector(`#block-${clientId} .cc-gallery`);
    window['gallery-' + clientId] = new Isotope(grid, {
        itemSelector: '.cc-gallery-card',
        percentPosition: true,
    });
}