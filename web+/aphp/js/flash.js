function dismissFlashpanel()
{
    $('#flash_spacer, #flash_panel').slideUp("fast"
        , function() {
            $('#flash_spacer, #flash_panel').hide();
        });
}