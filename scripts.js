jQuery(document).ready(function ($) {

        jQuery("#home_btn").click(function () {

                jQuery('#product_data').remove();
                jQuery("#header_div").hide();
                jQuery("#upload_div").show();
                jQuery("#product_div").show();
        });

        jQuery("#export_csv_btn").click(function () {

                var myForm = jQuery('#products_form')[0];
                var formData = new FormData(myForm);

                formData.append("action", 'exportCSV');
                formData.append("table_name", jQuery('#products_table').attr('name'));

                jQuery('#export_table_name').val(jQuery('#products_table').attr('name'));

                jQuery.ajax({
                        url: 'routing.php',
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
                                jQuery('#export_file')[0].submit();
                        }
                });
        });

        jQuery(".gen_table_btn").click(function () {

                var myForm = jQuery('#products_form')[0];
                var formData = new FormData(myForm);
                var filters = [];
                filters.push(getFilters('all'));

                formData.append("action", 'showProducts');
                formData.append("table_name", this.name);
                formData.append("ipp", jQuery("#items_per_page").val());
                formData.append("filter", filters);//jQuery(".filter_type").val());
                formData.append("current_row", jQuery('#current_row').val()); // for current_row read group_id

                jQuery('#products_table').attr('name', this.name);
                jQuery('#next_page_btn').attr('name', this.name);
                jQuery('#prev_page_btn').attr('name', this.name);
                jQuery('#items_per_page').attr('name', this.name);

                jQuery('#file_form_div').show();

                jQuery.ajax({
                        url: 'routing.php',
                        type: 'post',
                        data: formData,
                        // Tell jQuery not to process data or worry about content-type. You *must* include these options!
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: "json",
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
                                jQuery('#upload_div').hide();
                                jQuery('#product_div').append(data.html);
                                jQuery('#next_page_btn').show();
                                jQuery('#current_row').val(data.row);
                        }
                });
        });

        jQuery(".gen_btn").click(function () {

                var myForm = jQuery('#existing_files_form')[0];
                var formData = new FormData(myForm);
                formData.append("filename", this.name);
                formData.append("action", 'useFile');

                jQuery.ajax({
                        url: 'routing.php',
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
                formData.append("action", 'checkUploadFile');
                var filename = this.value;

                jQuery.ajax({
                        url: 'routing.php',
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
                                jQuery('#upload_file_name').text(filename);
                        }
                });
        });

        jQuery('#fileToExport').change(function () {

                var myForm = jQuery('#export_file')[0];
                var formData = new FormData(myForm);

                formData.append("table_name", jQuery('#products_table').attr('name'));
                formData.append("filename", this.value);
                formData.append("action", 'checkExportFile');


                var filename = this.value;

                jQuery.ajax({
                        url: 'routing.php',
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
                                        jQuery('#export_csv_btn').show();
                                }

                                var index = filename.lastIndexOf("\\") + 1;
                                filename = "File : " + filename.substr(index);
                                jQuery('#export_file_name').text(filename);
                        }
                });
        });

        jQuery("#import_alter_ego_btn").click(function () {

                var myForm = jQuery('#upload_file')[0];
                var formData = new FormData(myForm);

                var filters = [];
                filters.push(getFilters('all'));

                formData.append("action", 'importAlterEgo');

//        formData.append("table_name", this.name);
                formData.append("ipp", jQuery("#items_per_page").val());
                formData.append("filter", filters);// jQuery(".filter_type").val());
                formData.append("current_row", jQuery('#current_row').val()); // for current_row read group_id

//        jQuery('#products_table').attr('name', this.name);
//        jQuery('#next_page_btn').attr('name', this.name);
//        jQuery('#prev_page_btn').attr('name', this.name);
//        jQuery('#items_per_page').attr('name', this.name);

                jQuery.ajax({
                        url: 'routing.php',
                        type: 'post',
                        data: formData,
                        // Tell jQuery not to process data or worry about content-type. You *must* include these options!
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: "json",
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
                        error: function (jqXHR, textStatus, errorThrown) {
                                jQuery('#product_div').append(jqXHR.responseText);
                        },
                        success: function (data) {
                                jQuery('#product_data').remove();
                                jQuery('#upload_div').hide();
                                jQuery('#product_div').append(data.html);
                                jQuery('#next_page_btn').show();
                                jQuery('#current_row').val(data.row);
                        }
                });
        });

        jQuery("#upload_file_btn").click(function () {

                var myForm = jQuery('#upload_file')[0];
                var formData = new FormData(myForm);
                formData.append("action", jQuery('#upload_file').attr("action"));

                jQuery.ajax({
                        url: 'routing.php',
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
                        url: 'routing.php',
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

        jQuery("#next_page_btn").click(function () {

                var myForm = jQuery('#products_form')[0];
                var formData = new FormData(myForm);
                var filters = [];
                filters.push(getFilters('current'));

                formData.append("action", 'nextPage');
                formData.append("table_name", this.name);
                formData.append("ipp", jQuery("#items_per_page").val());
                formData.append("filter", filters);//jQuery("#filter_type").val());
                formData.append("current_row", jQuery('#current_row').val());

                jQuery.ajax({
                        url: 'routing.php',
                        type: 'post',
                        data: formData,
                        // Tell jQuery not to process data or worry about content-type. You *must* include these options!
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: "json",
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
                                jQuery('#prev_page_btn').show();
                                jQuery('#product_div').append(data.html);
                                jQuery('#current_row').val(data.row);
                        }
                });
        });

        jQuery("#prev_page_btn").click(function () {

                var myForm = jQuery('#products_form')[0];
                var formData = new FormData(myForm);
                var filters = [];
                filters.push(getFilters('current'));

                formData.append("action", 'previousPage');
                formData.append("table_name", this.name);
                formData.append("ipp", jQuery("#items_per_page").val());
                formData.append("filter", filters);// jQuery("#filter_type").val());
                formData.append("current_row", jQuery('#current_row').val());

                jQuery.ajax({
                        url: 'routing.php',
                        type: 'post',
                        data: formData,
                        // Tell jQuery not to process data or worry about content-type. You *must* include these options!
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: "json",
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
                                jQuery('#product_div').append(data.html);
                                jQuery('#current_row').val(data.row);
                                if (data.row == 0) {
                                        jQuery('#prev_page_btn').hide();
                                }
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

        jQuery("#items_per_page").change(function () {

                var myForm = jQuery('#products_form')[0];
                var formData = new FormData(myForm);
                var filters = [];
                filters.push(getFilters('current'));

                formData.append("action", 'changeIPP');
                formData.append("table_name", this.name);
                formData.append("ipp", jQuery("#items_per_page").val());
                formData.append("filter", filters);//jQuery("#filter_type").val());
                formData.append("current_row", jQuery('#current_row').val());

                jQuery.ajax({
                        url: 'routing.php',
                        type: 'post',
                        data: formData,
                        // Tell jQuery not to process data or worry about content-type. You *must* include these options!
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: "json",
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
                                jQuery('#product_div').append(data.html);
//                jQuery('#current_row').val(data.new_row);
                        }
                });
        });

        jQuery(".filter_type").change(function () {

                var filters = getFilters(this.id);
//        if (this.id === "all") {
//            jQuery('.filter_type').not('#all').prop("checked", false);
//        } else {
//            jQuery('#all').prop("checked", false);
//        }
//        jQuery('#filters :checked').each(function () {
//            filters.push($(this).val());
//        });

                var myForm = jQuery('#products_form')[0];
                var formData = new FormData(myForm);

                formData.append("action", 'changeFilter');
//        formData.append("table_name", this.name);
                formData.append("filter", filters);//jQuery("#filter_type").val());
                formData.append("ipp", jQuery("#items_per_page").val());
                formData.append("current_row", jQuery('#current_row').val());

                jQuery.ajax({
                        url: 'routing.php',
                        type: 'post',
                        data: formData,
                        // Tell jQuery not to process data or worry about content-type. You *must* include these options!
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: "json",
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
                                jQuery('#product_div').append(data.html);
//                jQuery('#current_row').val(data.new_row);
                        }
                });
        });

        jQuery("#product_div").on('change', '.selling_checkbox', function () {

//        var myForm = jQuery('#products_form')[0];
                var formData = new FormData();

                formData.append("action", 'updateSelling');
                formData.append("table_name", jQuery('#products_table').attr('name'));

                var sellingList = {};

                var id = jQuery(this).attr('data-id');
                var isChecked = jQuery(this).prop('checked') ? true : false;
                sellingList['id'] = id;
                sellingList['checked'] = isChecked;

                var str = JSON.stringify(sellingList);
                formData.append("selling", str);

                jQuery.ajax({
                        url: 'routing.php',
                        type: 'post',
                        data: formData,
                        // Tell jQuery not to process data or worry about content-type. You *must* include these options!
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: "json",
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

                        }
                });
        });

        function getFilters(id = false) {

                var filters = [];
                if (jQuery('#filters :checked').length === 0) {
                        id = 'all';
                }
                if (id === 'all') {
                        jQuery('.filter_type').not('#all').prop("checked", false);
                        jQuery('#all').prop("checked", true);
                        filters[0] = "All";
                } else if (id === "current") {
                        jQuery('#filters :checked').each(function () {
                                filters.push($(this).val());
                        });
                } else {
                        jQuery('#all').prop("checked", false);
                        jQuery('#filters :checked').each(function () {
                                filters.push($(this).val());
                        });
                }
                return filters;
        }

});
