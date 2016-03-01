jQuery('[data-dt-importer]').each(function() {
    var $this   =   jQuery(this),
        item    =   $this,
        content =   $this.find('.dt-importer-response');

    $this.find('[data-import]').click(function(e) {

        var $this   = jQuery(this),
            demo    = $this.data('import'),
            nonce   = $this.data('nonce');
        e.preventDefault();
        var data = {
            action: 'dt_demo_importer',
            nonce: nonce,
            id: demo
        };
        jQuery.post(ajaxurl, data, function(response){
            content.addClass('active');
            content.append(response);
            item.addClass('imported');
            $this.html("Re-Import");
        });
    });

    jQuery('.dismiss').click(function() {
        content.removeClass('active');
    });

});
