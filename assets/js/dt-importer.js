jQuery('[data-dt-importer]').each(function() {
    var $this   =   jQuery(this),
        item    =   $this,
        tag     =   $this.find('.dt-tag'),
        content =   $this.find('.dt-importer-response');

    $this.find('[data-import]').click(function(e) {
        e.preventDefault();
        var $this   = jQuery(this),
            demo    = $this.data('import'),
            nonce   = $this.data('nonce'),
            data = {
                action: 'dt_demo_importer',
                nonce: nonce,
                id: demo
            };

            $this.html('<span class="spinner">Please Wait...</span>');
        jQuery.post(ajaxurl, data, function(response){
            content.addClass('active');
            content.append(response);
            item.addClass('imported');
            $this.html("Re-Import");
            tag.html("Imported");
        });
    });

    jQuery('.dismiss').click(function() {
        content.removeClass('active');
    });

});
