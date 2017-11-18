jQuery(document).ready(function ($) {

    jQuery("#home_btn").click(function () {

        jQuery('#product_data').remove();
        jQuery("#header_div").hide();
        jQuery("#upload_div").show();
        jQuery("#product_div").show();
    });

    jQuery(".gen_table_btn").click(function () {

        var myForm = jQuery('#products_form')[0];
        var formData = new FormData(myForm);
        formData.append("table_name", this.name);
        formData.append("action", 'showProducts');
        
         jQuery('#current_row').val(jQuery('#current_row').val() + 5);
         formData.append("row", jQuery('#current_row').val());

        jQuery.ajax({
            url: 'main.php',
            type: 'post',
            data: formData,
            // Tell jQuery not to process data or worry about content-type. You *must* include these options!
            cache: false,
            contentType: false,
            processData: false,
            // Custom XMLHttpRequest
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            $('progress').attr({
                                value: e.loaded,
                                max: e.total,
                            });
                        }
                    }, false);
                }
                return myXhr;
            },
            success: function (data) {
                jQuery('#upload_div').hide();
                jQuery('#product_div').append(data);
                jQuery('#next_page_btn').show();
            }
        });
    });

    jQuery(".gen_btn").click(function () {

        var myForm = jQuery('#existing_files_form')[0];
        var formData = new FormData(myForm);
        formData.append("filename", this.name);
        formData.append("action", 'useFile');

        jQuery.ajax({
            url: 'main.php',
            type: 'post',
            data: formData,
            // Tell jQuery not to process data or worry about content-type. You *must* include these options!
            cache: false,
            contentType: false,
            processData: false,
            // Custom XMLHttpRequest
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            $('progress').attr({
                                value: e.loaded,
                                max: e.total,
                            });
                        }
                    }, false);
                }
                return myXhr;
            },
            success: function (data) {
                jQuery('#upload_div').hide();
                jQuery('#header_div').show();
                jQuery('#header_form').append(data);
            }
        });
    });

    jQuery('#uploadedfile').change(function () {

        var formData = new FormData();
        formData.append("filename", this.value);
        formData.append("action", 'checkFile');
        var filename = this.value;

        jQuery.ajax({
            url: 'main.php',
            type: 'post',
            data: formData,
            // Tell jQuery not to process data or worry about content-type. You *must* include these options!
            cache: false,
            contentType: false,
            processData: false,
            // Custom XMLHttpRequest
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            $('progress').attr({
                                value: e.loaded,
                                max: e.total,
                            });
                        }
                    }, false);
                }
                return myXhr;
            },
            success: function (data) {

                if (data == 'true') {
                    jQuery('#file_exists_lbl').show();
                    jQuery('#use_file_btn').show();
                    jQuery('#upload_file_btn').show();
                } else {
                    jQuery('#upload_file_btn').show();
                }

                var index = filename.lastIndexOf("\\") + 1;
                filename = "File : " + filename.substr(index);
                jQuery('#file_name').text(filename);
            }
        });
    });

    jQuery("#use_file_btn").click(function () {

        var myForm = jQuery('#upload_file')[0];
        var formData = new FormData(myForm);
        formData.append("action", 'useFile');

        jQuery.ajax({
            url: 'main.php',
            type: 'post',
            data: formData,
            // Tell jQuery not to process data or worry about content-type. You *must* include these options!
            cache: false,
            contentType: false,
            processData: false,
            // Custom XMLHttpRequest
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            $('progress').attr({
                                value: e.loaded,
                                max: e.total,
                            });
                        }
                    }, false);
                }
                return myXhr;
            },
            success: function (data) {
                jQuery('#upload_div').hide();
                jQuery('#header_div').show();
                jQuery('#header_form').append(data);
            }
        });
    });

    jQuery("#upload_file_btn").click(function () {

        var myForm = jQuery('#upload_file')[0];
        var formData = new FormData(myForm);
        formData.append("action", jQuery('#upload_file').attr("action"));

        jQuery.ajax({
            url: 'main.php',
            type: 'post',
            data: formData,
            // Tell jQuery not to process data or worry about content-type. You *must* include these options!
            cache: false,
            contentType: false,
            processData: false,
            // Custom XMLHttpRequest
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            $('progress').attr({
                                value: e.loaded,
                                max: e.total,
                            });
                        }
                    }, false);
                }
                return myXhr;
            },
            success: function (data) {
                jQuery('#upload_div').hide();
                jQuery('#header_div').show();
                jQuery('#header_form').append(data);
            }
        });
    });

    jQuery("#select_header_btn").click(function () {

        var myForm = jQuery('#header_form')[0];
        var formData = new FormData(myForm);
        formData.append("action", jQuery('#header_form').attr("action"));

        jQuery.ajax({
            url: 'main.php',
            type: 'post',
            data: formData,
            // Tell jQuery not to process data or worry about content-type. You *must* include these options!
            cache: false,
            contentType: false,
            processData: false,
            // Custom XMLHttpRequest
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            $('progress').attr({
                                value: e.loaded,
                                max: e.total,
                            });
                        }
                    }, false);
                }
                return myXhr;
            },
            success: function (data) {
                jQuery('#header_div').hide();
                jQuery('#product_div').show();
                location.reload();
            }
        });
    });

    /**
     jQuery("#show_products_btn").click(function () {

        var myForm = jQuery('#products_form')[0];
        var formData = new FormData(myForm);
        formData.append("action", jQuery('#products_form').attr("action"));
        formData.append("row", jQuery('#current_row').val());

        jQuery.ajax({
            url: 'main.php',
            type: 'post',
            data: formData,
            // Tell jQuery not to process data or worry about content-type. You *must* include these options!
            cache: false,
            contentType: false,
            processData: false,
            // Custom XMLHttpRequest
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            $('progress').attr({
                                value: e.loaded,
                                max: e.total,
                            });
                        }
                    }, false);
                }
                return myXhr;
            },
            success: function (data) {
                jQuery('#product_div').append(data);
                jQuery('#show_products_btn').hide();
            }
        });
    });*/

    jQuery("#next_page_btn").click(function () {

        var myForm = jQuery('#products_form')[0];
        var formData = new FormData(myForm);
//        formData.append("action", jQuery('#products_form').attr("action"));
        formData.append("action", 'nextPage');
//        jQuery('#current_row').val(jQuery('#current_row').val() + 5);
        formData.append("row", jQuery('#current_row').val());
        

        jQuery.ajax({
            url: 'main.php',
            type: 'post',
            data: formData,
            // Tell jQuery not to process data or worry about content-type. You *must* include these options!
            cache: false,
            contentType: false,
            processData: false,
            // Custom XMLHttpRequest
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            $('progress').attr({
                                value: e.loaded,
                                max: e.total,
                            });
                        }
                    }, false);
                }
                return myXhr;
            },
            success: function (data) {
                jQuery('#product_data').remove();
                jQuery('#product_div').append(data);
//                jQuery('#current_row').val(data.new_row);
            }
        });
    });

    jQuery("#prev_page_btn").click(function () {

        var myForm = jQuery('#products_form')[0];
        var formData = new FormData(myForm);
        formData.append("action", jQuery('#products_form').attr("action"));
        if (jQuery('#current_row').val() < 5) {
            jQuery('#current_row').val(0);
        } else {
            jQuery('#current_row').val(jQuery('#current_row').val() - 5);
        }
        formData.append("row", jQuery('#current_row').val());

        jQuery.ajax({
            url: 'main.php',
            type: 'post',
            data: formData,
            // Tell jQuery not to process data or worry about content-type. You *must* include these options!
            cache: false,
            contentType: false,
            processData: false,
            // Custom XMLHttpRequest
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            $('progress').attr({
                                value: e.loaded,
                                max: e.total,
                            });
                        }
                    }, false);
                }
                return myXhr;
            },
            success: function (data) {
                jQuery('#product_data').remove();
                jQuery('#product_div').append(data);
            }
        });
    });

    jQuery("#product_div").on('click', '.image-slide-btn', function () {
        
        var direction = $(this).attr("data-direction");
        var image_list = $(this).parent().find("ul");
        var num_images = $(image_list).attr("data-count");
        
         if (num_images > 1) {
            if (direction === 'left') {

                $(image_list).css({marginLeft: -250});
                $(image_list).find("li:first").before($(image_list).find("li:last"));
                $(image_list).animate({marginLeft: 0}, 2000, function () {
                    $(image_list).css({marginLeft: 0});
                });
            } else {
                $(image_list).animate({marginLeft: -250}, 2000, function () {
                    $(this).find("li:last").after($(this).find("li:first"));
                    // Now we've taken out the left-most list item, reset the margin
                    $(this).css({marginLeft: 0});
                });
            }
        }
    });

    jQuery("#product_div").on('change', '.selling_checkbox', function () {
        var id = $(this).attr("data-id");
        alert(id);
        
    });
    
     jQuery("#items_per_page").change( function () {
        
        var formData = new FormData();
        
        var items_per_page =  this.val();
        formData.append("row", jQuery('#current_row').val());
        formData.append("ipp", items_per_page);
        formData.append("action", 'showProducts');
        
         jQuery.ajax({
            url: 'main.php',
            type: 'post',
            data: formData,
            // Tell jQuery not to process data or worry about content-type. You *must* include these options!
            cache: false,
            contentType: false,
            processData: false,
            // Custom XMLHttpRequest
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    // For handling the progress of the upload
                    myXhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            $('progress').attr({
                                value: e.loaded,
                                max: e.total,
                            });
                        }
                    }, false);
                }
                return myXhr;
            },
            success: function (data) {
                jQuery('#product_data').remove();
                jQuery('#product_div').append(data);
//                jQuery('#current_row').val(data.new_row);
            }
        });
    });
    
//    jQuery("#show_tables_btn").click(function(){
//        
//        var formData = new FormData();
//         formData.append("action", 'showTables');
//         
//         jQuery.ajax({
//            url: 'main.php',
//            type: 'post',
//            data: formData,
//            // Tell jQuery not to process data or worry about content-type. You *must* include these options!
//            cache: false,
//            contentType: false,
//            processData: false,
//            // Custom XMLHttpRequest
//            xhr: function () {
//                var myXhr = $.ajaxSettings.xhr();
//                if (myXhr.upload) {
//                    // For handling the progress of the upload
//                    myXhr.upload.addEventListener('progress', function (e) {
//                        if (e.lengthComputable) {
//                            $('progress').attr({
//                                value: e.loaded,
//                                max: e.total,
//                            });
//                        }
//                    }, false);
//                }
//                return myXhr;
//            },
//            success: function (data) {
////                jQuery('#product_data').remove();
////                jQuery('#product_div').append(data);
//            }
//        });
//    });
    

});
