define(
    ['jquery', 'core/modal_factory'],
    function($, ModalFactory) {
        return {
            xhrs: {}, // List of xhr-requests. Used to abort.
            /**
             * Show the details of an item in a modal instead of following the link.
             * @param sender anchor who was clicked.
             */
            item_as_modal: function(sender) {
                console.log('mod_confman/main -> item_as_modal(sender)', sender);
                var tr = $(sender).closest('tr');
                $.ajax({
                    url: $(sender).attr('href') + '&embedded=1&noheader=1',
                    data: {},
                    success: function(data) {
                        //console.log(' => Response', data);
                        ModalFactory.create({
                            type: ModalFactory.types.OK,
                            title: tr.find('.title').html(),
                            body: data,
                        })
                        .then(function(modal) {
                            //console.log(' => Show modal');
                            modal.show();
                        });
                    },
                    dataType: 'html',
                });
            },
            /**
             * Prepare specific links to be opened in modal.
             * @param uniqid of table
             */
            item_as_modal_prepare: function(uniqid) {
                console.log('mod_confman/main -> item_as_modal_prepare(uniqid)', uniqid);
                $('#' + uniqid + '-items a.preview').attr('onclick', "var a = this; require(['mod_confman/main'], function(MAIN) { MAIN.item_as_modal(a); }); return false;");
            },
            item_file_mail: function(wwwroot, id, token, type) {
                console.log('mod_confman/main -> item_file_mail(wwwroot, id, token, type)', wwwroot, id, token, type);
                $.ajax({
                    url: wwwroot + '/mod/confman/ajax.php',
                    data: { act: 'file_mail', id: id, token: token, type: type },
                    success: function(data) { console.log(' => Response', data); },
                    dataType: 'html',
                });
            },
            /**
             * Removes a file from an item.
             */
            item_remove_file: function(a) {
                var MAIN = this;
                var domo = $(a).closest('div');
                var form = $(a).closest('form');

                var wwwroot = $(domo).attr('data-wwwroot');
                var filename = $(domo).find('.filename').attr('data-filename');
                var id = $(form).find('input[name="id"]').val();
                var token = $(form).find('input[name="token"]').val();
                console.log(wwwroot + '/mod/confman/ajax.php?act=file_delete&id=' + id + '&token=' + token + '&filename=' + filename);
                $.ajax({
                    url: wwwroot + '/mod/confman/ajax.php',
                    data: { act: 'file_delete', id: id, token: token, filename: filename },
                    success: function(data) {
                        console.log(' => Response', data);
                        try {
                            var result = JSON.parse(data);
                            if (typeof result.status !== 'undefined' && result.status == 'ok') {
                                console.log(' => File removed');
                                $(domo).remove();
                                MAIN.item_file_mail(wwwroot, id, token, 'file_delete');
                            } else {
                                alert('Error removing file');
                            }
                        } catch(e) { console.log('Could not analyze result', e); }
                    },
                    dataType: 'html',
                });
            },
            selectAllNone: function(uniqid) {
                var curstate = $('#' + uniqid + '-items').attr('data-selectall');
                if (curstate == '1') {
                    $('#' + uniqid + '-items').attr('data-selectall', '');
                    $('#' + uniqid + '-items .export>input').prop('checked', false);
                } else {
                    $('#' + uniqid + '-items').attr('data-selectall', '1');
                    $('#' + uniqid + '-items .export>input').prop('checked', true);
                }
            },
            selectApproved: function(uniqid) {
                $('#' + uniqid + '-items .approved .export>input').prop('checked', true);
            },
            /**
             * Toggles the approved status of an item.
             */
            set_approved: function(wwwroot, id, token, a) {
                console.log('mod_confman/main -> set_approved(wwwroot, id, token, a)', wwwroot, id, token, a);
                var row = $(a).closest('tr');
                var current = $(row).hasClass('approved');
                $.ajax({
                    url: wwwroot + '/mod/confman/ajax.php',
                    data: { act: 'set_approved', id: id, token: token, setto: (current) ? 0 : 1 },
                    success: function(data) {
                        console.log(' => Response', data);
                        try {
                            var result = JSON.parse(data);
                            if (typeof result.status !== 'undefined' && result.status == 'ok') {
                                console.log(' => Success');
                                if (typeof result.setto !== 'undefined' && result.setto) {
                                    $(row).addClass('approved');
                                    $(row).find('.approve img').attr('src', wwwroot + '/pix/i/completion-auto-pass.svg');
                                } else {
                                    $(row).removeClass('approved');
                                    $(row).find('.approve img').attr('src', wwwroot + '/pix/i/completion-auto-n.svg');
                                }
                            }
                        } catch(e) { console.log('Could not analyze result', e); }
                    },
                    dataType: 'html',
                });
            },
            /**
             * A File was selected by input. We start the uploading.
             * @param inp Source-Input
             * @param wwwroot of site
             * @param uniqid of form
             * @param id of confman_item
             * @param token (optional) opens access for non logged in users.
             */
            upload_file: function(inp, wwwroot, uniqid, id, token) {
                console.log('mod_confman/main -> upload_file(inp)', inp);
                var MAIN = this;
                //console.log(inp.files);return;
                for (var a = 0; a < inp.files.length; a++) {
                    var file = inp.files[a];
                    MAIN.upload_file_item(uniqid, wwwroot, id, token, file);
                }

                $(inp).val('');
            },
            /**
             * Creates a DOM-Object to show the file, its upload-progress and remove-link.
             * @param uniqid Uniqid of the form.
             * @param wwwroot of site.
             * @param filename Name of file.
             */
            upload_file_domo: function(uniqid, wwwroot, filename) {
                var domo = $('#mod_confman_form-' + uniqid + ' div[data-filename="' + filename + '"]');
                if (domo.length == 0) {
                    var domo = $('<div class="file_item" style="width: 100%;" data-filename="' + filename + '">').append([
                        $('<span class="progress" style="width: 20px; display: inline-block;">').append([
                            $('<span style="display: inline-block; background-color: lightblue;">').html('&nbsp;'),
                        ]),
                        $('<span class="remove" style="width: 20px; display: none;"><a href="#"><img src="/pix/t/delete.svg" alt="delete" /></a></span>'),
                        $('<span class="filename" style="width: calc(100% - 55px); display: inline-block; padding-left: 10px; overflow: hidden;">').attr('data-filename', filename).html(filename),
                    ]);
                    $('#mod_confman_form-' + uniqid).append(domo);
                }
                $(domo).attr('data-wwwroot', wwwroot);
                return domo;
            },
            /**
             * Handles upload-events for a specific file and connects to a particular li-element.
             * @param domo DOM-Object presenting the upload.
             * @param formData FormData-Object for Ajax-Query.
             * @param url url to send the file to.
             */
            upload_file_item: function(uniqid, wwwroot, id, token, file) {
                var MAIN = this;
                var reader  = new FileReader();
                reader.addEventListener("load", function () {
                    var formData = new FormData();
                    formData.append('act', 'file_append');
                    formData.append('id', id);
                    formData.append('token', token);
                    formData.append('filename', file.name);
                    formData.append('file', reader.result);

                    if (typeof MAIN.xhrs[file.name] !== 'undefined') {
                        MAIN.xhrs[file.name].abort();
                    }

                    var domo = MAIN.upload_file_domo(uniqid, wwwroot, file.name);
                    MAIN.upload_file_prepare_uploading(domo);
                    var xhr = new XMLHttpRequest();
                    // Set up events
                    xhr.upload.addEventListener('loadstart', function(event) {
                        console.log('mod_confman/main -> upload_file_item_event_start(event)', event);

                    }, false);
                    xhr.upload.addEventListener('progress', function(event) {
                        console.log('mod_confman/main -> upload_file_item_event_progress(event)', event);
                        var progress = Math.floor(event.loaded / (event.total / 100));
                        console.log(' => Progress is now ', progress, '%');
                        $(domo).find('.progress>span').css('width', progress + '%');
                    }, false);
                    xhr.upload.addEventListener('load', function(event) {
                        console.log('mod_confman/main -> upload_file_item_event_load(event)', event);

                    }, false);
                    xhr.addEventListener('readystatechange', function(event) {
                        console.log('mod_confman/main -> upload_file_item_event_readystatechange(event)', event);
                        if (event.target.readyState == 4) {
                            var filename = $(domo).find('.filename').attr('data-filename');
                            if (typeof MAIN.xhrs[filename] !== 'undefined') {
                                delete(MAIN.xhrs[filename]);
                            }
                            switch (event.target.status) {
                                case 200:
                                    try {
                                        var f = JSON.parse(xhr.responseText);
                                        if (typeof f.url !== 'undefined') {
                                            // File uploaded properly and we got a response.
                                            $(domo).find('.filename').attr('data-url', f.url);
                                            MAIN.upload_file_prepare_uploaded(domo);
                                            MAIN.item_file_mail(wwwroot, id, token, 'file_append');
                                        }
                                    } catch(e) {
                                        console.log(' => Invalid result was', xhr.responseText)
                                    }
                                break;
                                case 404:
                                    alert('HTTP-Error 404');
                                break;
                                default:
                                    alert('HTTP-Status ' + event.target.status);
                            }
                        }
                    }, false);
                    // Set up request
                    xhr.open('POST', wwwroot + '/mod/confman/ajax.php', true);
                    // Fire!
                    MAIN.xhrs[domo.find('.filename').html()] = xhr;
                    xhr.send(formData);
                }, false);
                reader.readAsDataURL(file);
            },
            /**
             * Prepares the HTML-Form and lists current files.
             * @param uniqid of mustache.
             * @param files as JSON.
             */
            upload_file_prepare: function(uniqid, wwwroot, files) {
                console.log('mod_confman/main -> upload_file_prepare(uniqid, wwwroot, files)', uniqid, wwwroot, files);
                files = JSON.parse(files);
                for (var a = 0; a < files.length; a++) {
                    var domo = this.upload_file_domo(uniqid, wwwroot, files[a].filename);
                    $(domo).find('.filename').attr('data-url', files[a].url);
                    this.upload_file_prepare_uploaded(domo);
                }
            },
            /**
             * Switches a domo to status upload was complete, set link to file.
             */
            upload_file_prepare_uploaded: function(domo) {
                // File uploaded properly and we got a response.
                $(domo).find('.remove').css('display', 'inline-block');
                $(domo).find('.remove>a').attr('onclick', 'var a = this; require(["mod_confman/main"], function(MAIN) { MAIN.item_remove_file(a); }); return false;');
                $(domo).find('.progress').css('display', 'none');
                var filename = $(domo).find('.filename').attr('data-filename');
                var fileurl = $(domo).find('.filename').attr('data-url');
                $(domo).find('.filename').html('<a href="' + fileurl + '" target="_blank">' + filename + '</a>');
            },
            /**
             * Switches a domo to upload pending.
             */
            upload_file_prepare_uploading: function(domo) {
                $(domo).find('.filename').html($(domo).find('.filename').attr('data-filename'));
                $(domo).find('.progress').css('display', 'inline-block').children().css('width', '0%');
                $(domo).find('.remove').css('display', 'none');
            },
        }
    }
);
