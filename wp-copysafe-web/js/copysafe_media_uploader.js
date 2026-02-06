var sendEditorTimeout = null;
jQuery(document).ready(function($) {
  $(document).off('click', '#wpcsw_cancel');
  $(document).on("click", "#wpcsw_cancel" , function() {
    $('#wpcsw_file_details').html("");
  });

  $(document).off('click', '#wpcsw_div .ui-tabs-anchor');
  $(document).on('click', '#wpcsw_div .ui-tabs-anchor',function () {
    var iid = $(this).attr("id");
    iid = iid.substring(0, iid.length - 3);
    $("#wpcsw_div .ui-tabs-panel").hide();
    $("#" + iid).show();
    $(this).parents(".ui-tabs-nav").children(".ui-state-default").removeClass("ui-state-active");
    $(this).parent().addClass("ui-state-active");
  });

  //----------------------------------------
  var max_size = $("#wpcsw-upload-max-size").val();

  var wpcsw_process_setting = function (frm, status) {
    if (status == "start") $("#wpcsw_ajax_process").show();
    if (status == "end") $("#wpcsw_ajax_process").hide();
    if (frm == "load") {
      if (status == "start") {
        $("#wpcsw_message").html("");
        $('input:button').attr("disabled", true);
      }
      if (status == "end") {
        $("#wpcsw-custom-queue").html("No file chosen");
        $('input:button').attr("disabled", false);
      }
    }

    if (frm == "search") {
      if (status == "start") {
        $("#wpcsw-search").attr("disabled", true);
      }
      if (status == "end") {
        $("#wpcsw-search").attr("disabled", false);
      }
    }
  }

  if($('.wpcsw-mfu-plugin-uploader').length > 0) {
    var container = $('.wpcsw-mfu-plugin-uploader');
    var wpcsw_upload_nonce_value = container.find('.ajaxnonce').attr('id');

    var wpcsw_uploader = new plupload.Uploader({
      browse_button: 'wpcsw-plugin-uploader-button',
      runtimes: 'html5,flash,silverlight,gears,html4',
      flash_swf_url: '/wp-includes/js/plupload/plupload.flash.swf',
      silverlight_xap_url: '/wp-includes/js/plupload/plupload.silverlight.xap',
      max_file_size: max_size + 'b',
      urlstream_upload: true,
      file_data_name: 'async-upload',
      multipart: true,
      multi_selection: false,
      resize: {width: 300, height: 300, quality: 90},
      multipart_params: {
        _ajax_nonce: wpcsw_upload_nonce_value,
        action: 'wpcsw-plugin-upload-action'
      },
      url: 'admin-ajax.php',
      filters: [{title: "Class files", extensions: "class"}]
    });
    wpcsw_uploader.init();

    // EVENTS
    // init
    wpcsw_uploader.bind('Init', function (up) {
      $('#wpcsw-progress-bar').progressbar({
        value: 0
      });
    });
  
    // error
    wpcsw_uploader.bind('Error', function (up, args) {
      if( args["code"] == '-600' ){
        $("#wpcsw_message").html('<div class="error"><p>'+args["message"]+' <b>Please upload file less than '+global_uploader_options.max_file_size+' of size.</b></p></div>');
      }
      if( args["code"] == '-601' ){
        $("#wpcsw_message").html('<div class="error"><p>'+args["message"]+' <b>Please upload only .class file.</b></p></div>');
      }
    });

    // file added
    wpcsw_uploader.bind('FilesAdded', function (up, files) {
      $.each(files, function (i, file) {
        $("#wpcsw-upload-filename").html(file.name);
        $("#wpcsw-upload-status").html("Upload Started");
      });

      up.refresh();
      up.start();
    });

    // upload progress
    wpcsw_uploader.bind('UploadProgress', function (up, file) {
      $("#wpcsw-progress-bar").progressbar({
        value: file.percent
      });
    });

    // file uploaded
    wpcsw_uploader.bind('FileUploaded', function (up, file, response) {
      response = $.parseJSON(response.response);
      if (response['status'] == 'success') {
        $("#wpcsw-upload-status").html("Upload Complete");

        var file_name = file.name;
        let request = {
          action: 'wpcsw_ajaxprocess',
          fucname: 'file_upload',
          _nonce: wpcsw_upload_nonce_value,
        };
        $.post(ajaxurl, request, function (param) {
          wpcsw_process_setting("load", "end");
          var contents = $.parseJSON(param);
          $("#wpcsw_message").html(contents["message"]);
          $("#wpcsw_upload_list").html(contents["list"]);
          $("#wpcsw-tabs-2-bt").trigger("click");
          $("#wpcsw_searchfile").val(file_name);
          $("#wpcsw-search").trigger("click");
        });
      }
      else {
        $("#wpcsw-upload-status").html("Error Uploading File");
      }
    });
  }

  $(document).off('click', '#wpcsw-search');
  $(document).on('click', '#wpcsw-search', function () {
    let file_name = $("#wpcsw_searchfile").val();
    let post_id = WPCSW_EDITOR.getPostId();
    if (!file_name) {
      alert('Type a file name');
      $("#wpcsw_searchfile").focus();
    } else {
      let request = {
        action: 'wpcsw_ajaxprocess',
        fucname: 'file_search',
        search: file_name,
        post_id: post_id
      };
      wpcsw_process_setting("search", "start");
      $.post(ajaxurl, request, function (param) {
        wpcsw_process_setting("search", "end");
        $('#wpcsw_file_details').html(param);
      });
    }
  });

  $(document).off('click', '#wpcsw_setting_save');
  $(document).on("click", "#wpcsw_setting_save" , function() {
    let setData = {};
    let nonce = $('#wpcsw_setting_save_nonce').val();
    $("#wpcsw_setting_body input").each(function () {
      let field_name = $(this).attr("name");
      setData[field_name] = $(this).val();
    });
    let request = {
      action: 'wpcsw_ajaxprocess',
      fucname: 'setting_save',
      post_id: WPCSW_EDITOR.getPostId(),
      nname: $("#wpcsw_searchfile").val(),
      set_data: JSON.stringify(setData),
      _nonce: nonce,
    };
    wpcsw_process_setting("setting", "start");
    $.post(ajaxurl, request, function (param) {
      $("#wpcsw_message").html(param);
      wpcsw_process_setting("setting", "end");
      WPCSW_EDITOR.sendToEditor($("#wpcsw_searchfile").val());
    });
  });

  $(document).off('click', '#wpcsw_setting_body img');
  $(document).on("click", "#wpcsw_setting_body img" , function() {
    alert($(this).attr("alt"));
  });
});