<script>

    let taa_mna = document.getElementById( "taa-modal-new-account"         );
    let taa_mnp = document.getElementById( "taa-modal-new-password"        );
    let taa_mva = document.getElementById( "taa-modal-validate-account"    );
    let taa_mvc = document.getElementById( "taa-modal-validate-code"       );

    let taa_bna = document.getElementById( "taa-btn-form-new-account"      );
    let taa_bnp = document.getElementById( "taa-btn-form-new-password"     );
    let taa_bva = document.getElementById( "taa-btn-form-validate-account" );
    let taa_bvc = document.getElementById( "taa-btn-form-validate-code"    );

    let taa_ena = document.getElementById( "taa-error-new-acc"             );
    let taa_enp = document.getElementById( "taa-error-new-pass"            );
    let taa_eva = document.getElementById( "taa-error-validate-acc"        );
    let taa_evc = document.getElementById( "taa-error-validate-code"       );

    jQuery(document).ready(function() {

        const queryString = window.location.search;
        const urlParams   = new URLSearchParams( queryString );
        const code        = urlParams.get( "taac"  );
        const email       = urlParams.get( "email" );

        if ( code !== null )
        {
            switch ( code )
            {
                case "acc":
                    jQuery( taa_mva ).removeClass( "taa-d-none" );
                    jQuery( "#taa-validate-acc-email" ).val( email );
                break;

                case "code":
                    jQuery( taa_mvc ).removeClass( "taa-d-none" );
                    jQuery( "#taa-email" ).val( email );
                break;

                case "newacc":
                    jQuery( taa_mna ).removeClass( "taa-d-none" );
                    if ( email ) jQuery( "#taa-new-acc-email" ).val( email );
                break;
            }
        }
    });


    window.taaSubmitValCode = function ()
    {
        window.taaDisableBtns();

        jQuery( taa_evc ).addClass( "taa-d-none" );
        jQuery( "#taa-code" ).removeClass( "taa-error-border" );

        const code  = jQuery( "#taa-code" ).val();
        const email = jQuery( "#taa-email" ).val();

        if ( code != "" && code.length == 6 )
        {
            jQuery.ajax({
                url : "/wp-admin/admin-ajax.php",
                type: "POST",
                data: {
                    action : "taa_validate_code",
                    code   : code,
                    email  : email
                },
                success: function( data ) {

                    let result = JSON.parse( data );

                    if ( result.error )
                    {
                        window.taaEnableBtns();

                        jQuery( taa_evc ).removeClass( "taa-d-none" ).html( result.message );
                        jQuery( "#taa-code" ).addClass( "taa-error-border" );
                    }
                    else
                    {
                        switch (result.code)
                        {
                            case 2000:
                                window.location.href = window.location.href.split( "?" )[0];
                            break;

                            case 2001:
                                jQuery( taa_mvc ).addClass( "taa-d-none" );
                                window.taaEnableBtns();
                                jQuery( taa_mna ).removeClass( "taa-d-none" );
                                jQuery( "#taa-new-acc-email" ).val( email );
                            break;
                        }
                    }
                },
                error: function( err ) {
                    window.taaAjaxError( err );
                }
            });
        }
        else
        {
            window.taaEnableBtns();

            let errorMsg = ( code == "" ) ? "Insira o código recebido" : "O código tem 6 dígitos" ;
            jQuery( taa_evc ).removeClass( "taa-d-none" ).html( errorMsg );
            jQuery( "#taa-code" ).addClass( "taa-error-border" );
        }

    }

    window.taaSubmitNewAccount = function ()
    {
        window.taaDisableBtns();

        jQuery( taa_ena ).addClass( "taa-d-none" );
        jQuery( "#taa-new-acc-email" ).removeClass( "taa-error-border" );
        jQuery( "#taa-new-acc-pass1" ).removeClass( "taa-error-border" );
        jQuery( "#taa-new-acc-pass2" ).removeClass( "taa-error-border" );

        const email = jQuery( "#taa-new-acc-email" ).val();
        const pass1 = jQuery( "#taa-new-acc-pass1" ).val();
        const pass2 = jQuery( "#taa-new-acc-pass2" ).val();

        if ( email == "" )
        {
            window.taaEnableBtns();
            jQuery( taa_ena ).removeClass( "taa-d-none" ).html( "Informe um e-mail válido" );
            jQuery( "#taa-new-acc-email" ).addClass( "taa-error-border" );
        }
        else if ( pass1 != pass2 || pass1.length < 8 || pass2.length < 8 )
        {
            window.taaEnableBtns();
            jQuery( taa_ena ).removeClass( "taa-d-none" ).html( "As senhas devem ser idênticas e com 8 ou mais dígitos" );
            jQuery( "#taa-new-acc-pass1" ).addClass( "taa-error-border" );
            jQuery( "#taa-new-acc-pass2" ).addClass( "taa-error-border" );
        }
        else
        {
            jQuery.ajax({
                url : "/wp-admin/admin-ajax.php",
                type: "POST",
                data: {
                    action : "taa_new_account",
                    email  : email,
                    pass   : pass1
                },
                success: function( data ) {

                    let result = JSON.parse( data );

                    if ( result.error )
                    {
                        window.taaAjaxError( err );
                    }
                    else
                    {
                        window.location.href = window.location.href.split( "?" )[0];
                    }
                },
                error: function( err ) {
                    window.taaAjaxError( err );
                }
            });
        }
    }

    window.taaSubmitValAccount = function ()
    {
        window.taaDisableBtns();

        jQuery( taa_eva ).addClass( "taa-d-none" );
        jQuery( "#taa-validate-acc-email" ).removeClass( "taa-error-border" );
        jQuery( "#taa-validate-acc-pass"  ).removeClass( "taa-error-border" );

        const email = jQuery( "#taa-validate-acc-email" ).val();
        const pass  = jQuery( "#taa-validate-acc-pass"  ).val();

        if ( email == "" )
        {
            window.taaEnableBtns();
            jQuery( taa_eva ).removeClass( "taa-d-none" ).html( "Informe um e-mail válido" );
            jQuery( "#taa-validate-acc-email" ).addClass( "taa-error-border" );
        }
        else if ( pass == "" )
        {
            window.taaEnableBtns();
            jQuery( taa_eva ).removeClass( "taa-d-none" ).html( "Informe uma senha" );
            jQuery( "#taa-validate-acc-pass" ).addClass( "taa-error-border" );
        }
        else
        {
            jQuery.ajax({
                url : "/wp-admin/admin-ajax.php",
                type: "POST",
                data: {
                    action : "taa_validate_account",
                    email  : email,
                    pass   : pass
                },
                success: function( data ) {

                    let result = JSON.parse( data );

                    if ( result.error )
                    {
                        window.taaEnableBtns();

                        jQuery( taa_eva ).removeClass( "taa-d-none" ).html( "E-mail e/ou senha incorreta" );
                        jQuery( "#taa-validate-acc-email" ).addClass( "taa-error-border" );
                        jQuery( "#taa-validate-acc-pass"  ).addClass( "taa-error-border" );
                    }
                    else
                    {
                        window.location.href = window.location.href.split( "?" )[0];
                    }
                },
                error: function( err ) {
                    window.taaAjaxError( err );
                }
            });
        }
    }


    window.addEventListener( "click", function ( e ) {

        switch ( e.target.id )
        {
            case "taa-btn-form-new-account":
                e.preventDefault();
                window.taaSubmitNewAccount();
            break;

            case "taa-btn-form-validate-account":
                e.preventDefault();
                window.taaSubmitValAccount();
            break;

            case "taa-btn-form-validate-code":
                e.preventDefault();
                window.taaSubmitValCode();
            break;
        }

    }, false);





    // ==================================

    window.taaCloseModal = function ( e )
    {
        const id = e.id.replace("close", "modal");
        jQuery( `#${id}` ).addClass( "taa-d-none" );
    }

    window.taaDisableBtns = function ()
    {
        [ taa_bna, taa_bnp, taa_bva, taa_bvc ].forEach( ( e ) => {
            jQuery( e ).prop( "disabled", "disabled" ).find( "div" ).removeClass( "taa-d-none" );
        });
    }

    window.taaEnableBtns = function ()
    {
        [ taa_bna, taa_bnp, taa_bva, taa_bvc ].forEach( ( e ) => {
            jQuery( e ).prop( "disabled", null ).find( "div" ).addClass( "taa-d-none" );
        });
    }

    window.taaAjaxError = function ( err )
    {
        console.log( err );
        window.location.href = window.location.href.split( "?" )[0];
    }

</script>
