MappedRepairEvents.MobileFrontend = {
    
    initRegistration : function() {
        $('.half').on('click', function() {
            $(this).find('.description').slideDown('slow');
        });
    },
        
    getContentWidth : function() {
        return $('#content').width();
    },
    
    adaptPostDetail : function() {
        var imagesContainer = $('body.posts.detail .left');
        imagesContainer.show();
        $('body.posts.detail .right').append(imagesContainer);
    },
    
    adaptWorkshopDetail : function() {
        $('#tabs').css('margin-top', 0).find('ul').remove();
        var linkSelector = '.events-on-your-website-link';
        var containerMovedToBottomSelector = '.left';
        $(linkSelector).before($('.add-event-link'));
        $('.right').append('<div class="additional-buttons">' + $(containerMovedToBottomSelector).html() + '</div>');
        $(containerMovedToBottomSelector).remove();
        $('#tabs-3').show().insertAfter($('.additional-buttons'));
    },
    
    /**
     * .not(.no-hover) needs to be included in css
     */
    disableHoverOnSelector : function(selector) {
        $(selector).addClass('no-hover');
    },
    
    initAdaptHomeSlidesWidthListener : function(slides) {
        $(window).on('resize', function() {
            MappedRepairEvents.MobileFrontend.adaptHomeSlidesWidth(slides);
        });
        MappedRepairEvents.MobileFrontend.adaptHomeSlidesWidth(slides);
        $(slides).closest('.cycle-slideshow').show();
    },
    
    adaptHomeSlidesWidth : function(slides) {
        $(slides).each(function() {
            $(this).find('img').width(MappedRepairEvents.MobileFrontend.getContentWidth());
        });
    },
    
    initAdaptTeaserButtonSizeListener : function(teaserButtons, itemsPerRow) {
        $(window).on('resize', function() {
            MappedRepairEvents.MobileFrontend.adaptTeaserButtonSize(teaserButtons, itemsPerRow);
        });
        MappedRepairEvents.MobileFrontend.adaptTeaserButtonSize(teaserButtons, itemsPerRow);
        $(teaserButtons).closest('.teaser-buttons').show();
    },
    
    /**
     * image is background-image due to hover effect on desktop-version
     * and not wanting to use different images
     */
    adaptTeaserButtonSize : function(teaserButtons, itemsPerRow) {
        var spacer = itemsPerRow == 1 ? 0 : 10 / itemsPerRow;
        var newWidth = (MappedRepairEvents.MobileFrontend.getContentWidth() / itemsPerRow) - spacer;
        $(teaserButtons).each(function() {
            var newHeight = ($(this).height() / itemsPerRow) / $(this).width() * newWidth * itemsPerRow;
            $(this).width(newWidth);
            $(this).height(newHeight);
        });
    },
    
    putSaveAndCancelButtonToEndOfForm : function() {
        $('form').append($('form').find('.cancel-and-save-button-wrapper'));
    }
    
};