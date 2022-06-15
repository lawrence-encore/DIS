(function($) {
    'use strict';

    $(function() {
        if($('#country-datatable').length){
            initialize_country_table('#country-datatable');
        }

        initialize_click_events();
    });
})(jQuery);

function initialize_country_table(datatable_name, buttons = false, show_all = false){
    hide_multiple_buttons();
    
    var username = $('#username').text();
    var type = 'country table';
    var settings;

    var column = [ 
        { 'data' : 'CHECK_BOX' },
        { 'data' : 'COUNTRY_ID' },
        { 'data' : 'COUNTRY_NAME' },
        { 'data' : 'ACTION' }
    ];

    var column_definition = [
        { 'width': '1%','bSortable': false, 'aTargets': 0 },
        { 'width': '15%', 'aTargets': 1 },
        { 'width': '64%', 'aTargets': 2 },
        { 'width': '20%','bSortable': false, 'aTargets': 3 },
    ];

    if(show_all){
        length_menu = [ [-1], ['All'] ];
    }
    else{
        length_menu = [ [10, 25, 50, 100, -1], [10, 25, 50, 100, 'All'] ];
    }

    if(buttons){
        settings = {
            'ajax': { 
                'url' : 'system-generation.php',
                'method' : 'POST',
                'dataType': 'JSON',
                'data': {'type' : type, 'username' : username},
                'dataSrc' : ''
            },
            dom:  "<'row'<'col-sm-3'l><'col-sm-6 text-center mb-2'B><'col-sm-3'f>>" +  "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [
                'csv', 'excel', 'pdf'
            ],
            'order': [[ 1, 'asc' ]],
            'columns' : column,
            'scrollY': false,
            'scrollX': true,
            'scrollCollapse': true,
            'fnDrawCallback': function( oSettings ) {
                readjust_datatable_column();
            },
            'aoColumnDefs': column_definition,
            'lengthMenu': length_menu,
            'language': {
                'emptyTable': 'No data found',
                'searchPlaceholder': 'Search...',
                'search': '',
                'loadingRecords': '<div class="spinner-border spinner-border-lg text-info" role="status"><span class="sr-only">Loading...</span></div>'
            }
        };
    }
    else{
        settings = {
            'ajax': { 
                'url' : 'system-generation.php',
                'method' : 'POST',
                'dataType': 'JSON',
                'data': {'type' : type, 'username' : username},
                'dataSrc' : ''
            },
            'order': [[ 1, 'asc' ]],
            'columns' : column,
            'scrollY': false,
            'scrollX': true,
            'scrollCollapse': true,
            'fnDrawCallback': function( oSettings ) {
                readjust_datatable_column();
            },
            'aoColumnDefs': column_definition,
            'lengthMenu': length_menu,
            'language': {
                'emptyTable': 'No data found',
                'searchPlaceholder': 'Search...',
                'search': '',
                'loadingRecords': '<div class="spinner-border spinner-border-lg text-info" role="status"><span class="sr-only">Loading...</span></div>'
            }
        };
    }

    destroy_datatable(datatable_name);
    
    $(datatable_name).dataTable(settings);
}

function initialize_click_events(){
    var username = $('#username').text();

    $(document).on('click','#add-country',function() {
        generate_modal('country form', 'Country', 'R' , '1', '1', 'form', 'country-form', '1', username);
    });

    $(document).on('click','.update-country',function() {
        var country_id = $(this).data('country-id');

        sessionStorage.setItem('country_id', country_id);
        
        generate_modal('country form', 'Country', 'R' , '1', '1', 'form', 'country-form', '0', username);
    });
    
    $(document).on('click','.delete-country',function() {
        var country_id = $(this).data('country-id');
        var transaction = 'delete country';

        Swal.fire({
            title: 'Delete Country',
            text: 'Are you sure you want to delete this country?',
            icon: 'warning',
            showCancelButton: !0,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            confirmButtonClass: 'btn btn-danger mt-2',
            cancelButtonClass: 'btn btn-secondary ms-2 mt-2',
            buttonsStyling: !1
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    type: 'POST',
                    url: 'controller.php',
                    data: {username : username, country_id : country_id, transaction : transaction},
                    success: function (response) {
                        if(response === 'Deleted'){
                          show_alert('Delete Country', 'The country has been deleted.', 'success');

                          reload_datatable('#country-datatable');
                        }
                        else if(response === 'Not Found'){
                          show_alert('Delete Country', 'The country does not exist.', 'info');
                        }
                        else{
                          show_alert('Delete Country', response, 'error');
                        }
                    }
                });
                return false;
            }
        });
    });

    $(document).on('click','#delete-country',function() {
        var country_id = [];
        var transaction = 'delete multiple country';

        $('.datatable-checkbox-children').each(function(){
            if($(this).is(':checked')){  
                country_id.push(this.value);  
            }
        });

        if(country_id.length > 0){
            Swal.fire({
                title: 'Delete Multiple Countries',
                text: 'Are you sure you want to delete these countries?',
                icon: 'warning',
                showCancelButton: !0,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                confirmButtonClass: 'btn btn-danger mt-2',
                cancelButtonClass: 'btn btn-secondary ms-2 mt-2',
                buttonsStyling: !1
            }).then(function(result) {
                if (result.value) {
                    
                    $.ajax({
                        type: 'POST',
                        url: 'controller.php',
                        data: {username : username, country_id : country_id, transaction : transaction},
                        success: function (response) {
                            if(response === 'Deleted'){
                                show_alert('Delete Multiple Countries', 'The countries have been deleted.', 'success');
    
                                reload_datatable('#country-datatable');
                            }
                            else if(response === 'Not Found'){
                                show_alert('Delete Multiple Countries', 'The country does not exist.', 'info');
                            }
                            else{
                                show_alert('Delete Multiple Countries', response, 'error');
                            }
                        }
                    });
                    
                    return false;
                }
            });
        }
        else{
            show_alert('Delete Multiple Countries', 'Please select the countries you want to delete.', 'error');
        }
    });

}