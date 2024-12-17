(function () {

    var itemType = ['pa', 'pa_section', 'pa_column', 'pa_container'],
        itemTypeElementorHook = ['widget', 'column', 'section', 'container'];

    xsLocalStorage.init(
        {
            iframeUrl: "https://leap13.github.io/pa-cdcp/",
            initCallback: function () { }
        }
    );

    PACopyPasteHandler = {

        paste: function (checkType, element) {

            var container = null,
                element_location = {
                    index: 0
                },
                copied_widget_details = checkType.widgetCode,
                copied_widget_details_string = JSON.stringify(copied_widget_details);

            var copied_widget_type = copied_widget_details.elType,
                containMedia = /\.(jpg|png|jpeg|gif|svg)/gi.test(copied_widget_details_string),
                copied_widget = {
                    elType: copied_widget_type,
                    settings: copied_widget_details.settings
                },
                current_element = element,
                current_element_type = element.model.get("elType");

            switch (copied_widget_type) {
                case 'section':
                case "container":
                    copied_widget.elements = PACopyPasteHandler.generateUniqueID(copied_widget_details.elements);
                    container = elementor.getPreviewContainer();
                    switch (current_element_type) {
                        case 'widget':
                            element_location.index = current_element.getContainer().parent.parent.view.getOption("_index") + 1;
                            break;
                        case 'column':
                            element_location.index = current_element.getContainer().parent.view.getOption("_index") + 1;
                            break;
                        case 'section':
                            element_location.index = current_element.getOption("_index") + 1;
                            break;
                    }
                    break;
                case 'column':
                    copied_widget.elements = PACopyPasteHandler.generateUniqueID(copied_widget_details.elements);
                    switch (current_element_type) {
                        case 'widget':
                            container = current_element.getContainer().parent.parent;
                            element_location.index = current_element.getContainer().parent.view.getOption("_index") + 1;
                            break;
                        case 'column':
                            container = current_element.getContainer().parent;
                            element_location.index = current_element.getOption("_index") + 1;
                            break;
                        case 'section':
                            container = current_element.getContainer();
                            break;
                    }
                    break;
                case 'widget':
                    copied_widget.widgetType = copied_widget_details.widgetType;
                    container = current_element.getContainer();
                    switch (current_element_type) {
                        case 'widget':
                            container = current_element.getContainer().parent;
                            element_location.index = current_element.getOption("_index") + 1;
                            break;

                        case 'column':
                            container = current_element.getContainer();
                            break;

                        case 'section':
                            container = current_element.children.findByIndex(0).getContainer();
                            break;

                    }
                    break;
            }

            var new_element = $e.run("document/elements/create", {
                model: copied_widget,
                container: container,
                options: element_location
            });

            if (!new_element) {
                if ("widget" === current_element_type) {
                    if (current_element.$el.next('.undefined.elementor-widget-empty')) {
                        current_element.$el.next('.undefined.elementor-widget-empty').after('<div class="elementor-alert elementor-alert-warning">' + premium_cross_cp.widget_not_found + '</div>');
                    }
                } else {
                    if (current_element.$el.find('.undefined.elementor-widget-empty')) {
                        current_element.$el.find('.undefined.elementor-widget-empty').after('<div class="elementor-alert elementor-alert-warning">' + premium_cross_cp.widget_not_found + '</div>');
                    }
                }
            }

            if (containMedia) {
                jQuery.ajax({
                    url: premium_cross_cp.ajax_url,
                    method: "POST",
                    data: {
                        nonce: premium_cross_cp.nonce,
                        action: "premium_cross_cp_import",
                        copy_content: copied_widget_details_string
                    }
                }).done(function (response) {
                    if (response.success) {

                        var mediaElem = response.data[0];
                        copied_widget.elType = mediaElem.elType;
                        copied_widget.settings = mediaElem.settings;

                        if ("widget" === copied_widget.elType) {
                            copied_widget.widgetType = mediaElem.widgetType;
                        } else {
                            copied_widget.elements = mediaElem.elements;
                        }

                        $e.run("document/elements/delete", {
                            container: new_element
                        });

                        $e.run("document/elements/create", {
                            model: copied_widget,
                            container: container,
                            options: element_location
                        });
                    }
                })
            }

        },

        getData: function (allSections) {

            var allSectionsString = JSON.stringify(allSections);

            jQuery.ajax({
                url: premium_cross_cp.ajax_url,
                method: "POST",
                data: {
                    nonce: premium_cross_cp.nonce,
                    action: "premium_cross_cp_import",
                    copy_content: allSectionsString
                },
            }).done(function (e) {
                if (e.success) {

                    var data = e.data[0];
                    if (premium_cross_cp.elementorCompatible) {
                        // Compatibility for older elementor versions
                        elementor.sections.currentView.addChildModel(data)
                    } else {
                        elementor.previewView.addChildModel(data)
                    }

                    elementor.notifications.showToast({
                        message: elementor.translate('Content Pasted. Have Fun ;)')
                    });

                }
            }).fail(function () {
                elementor.notifications.showToast({
                    message: elementor.translate('Something went wrong!')
                });
            })
        },

        generateUniqueID: function (elements) {

            elements.forEach(function (item, index) {

                if (typeof elementorCommon.helpers.getUniqueId() != "undefined") {
                    item.id = elementorCommon.helpers.getUniqueId();
                } else {
                    item.id = elementor.helpers.getUniqueID();
                }

                if (item.elements.length > 0) {
                    PACopyPasteHandler.generateUniqueID(item.elements);
                }
            });

            return elements;
        },

        isValidJson: function (str) {
            try {
                JSON.parse(str);
                return true;
            } catch (e) {
                return false;
            }
        }

    }


    elementor.on('preview:loaded', function () {
        itemType.forEach(function (item, index) {
            elementor.hooks.addFilter('elements/' + itemTypeElementorHook[index] + '/contextMenuGroups', function (groups, element) {

                groups.push({
                    name: itemType[index],
                    actions: [{
                        name: "pa_copy",
                        title: "PA | Copy Element",
                        callback: function () {

                            var widgetType = element.model.get('widgetType'),
                                widgetCode = element.model.toJSON(),
                                copiedContent = {
                                    widgetType: widgetType,
                                    widgetCode: widgetCode
                                };

                            // Store data in local storage
                            xsLocalStorage.setItem('premium-c-p-element', JSON.stringify(copiedContent));

                            // Create a textarea, set its value to the JSON string, and copy to clipboard
                            var textarea = document.createElement('textarea');
                            textarea.value = JSON.stringify(copiedContent);
                            document.body.appendChild(textarea);
                            textarea.select();
                            document.execCommand('copy');
                            document.body.removeChild(textarea);

                        }
                    },
                    {
                        name: "pa_paste",
                        title: "PA | Paste Element",
                        callback: function () {

                            if (!navigator.clipboard.readText) {

                                var existingDialog = document.getElementById('pa-paste-area-dialog');
                                if (existingDialog) {
                                    existingDialog.parentNode.removeChild(existingDialog);
                                }

                                var paPasteElem = document.querySelector('#pa-paste-input');

                                if (!paPasteElem) {

                                    // Create a dialog for paste area
                                    var container = document.createElement('div'),
                                        paragraph = document.createElement('p');

                                    paragraph.innerHTML = 'Please grant clipboard permission for this site to paste.';

                                    var inputArea = document.createElement('input');
                                    inputArea.id = 'pa-paste-input';
                                    inputArea.type = 'text';
                                    inputArea.setAttribute('autocomplete', 'off');
                                    inputArea.setAttribute('autofocus', 'autofocus');
                                    inputArea.focus();

                                    container.appendChild(paragraph);
                                    container.appendChild(inputArea);

                                    // Handle paste event in the input area
                                    inputArea.addEventListener('paste', async function (event) {
                                        event.preventDefault();
                                        var pastedData = event.clipboardData.getData("text");

                                        if (PACopyPasteHandler.isValidJson(pastedData)) {

                                            var checktype = JSON.parse(pastedData);

                                            if (pastedData && typeof checktype == 'object') {
                                                // Call your paste handler function
                                                xsLocalStorage.setItem("premium-c-p-element", pastedData);
                                                PACopyPasteHandler.paste(checktype, element);
                                            }

                                        }

                                        var existingDialog = document.getElementById('pa-paste-area-dialog');
                                        if (existingDialog) {
                                            existingDialog.parentNode.removeChild(existingDialog);
                                        }

                                    });

                                    // Determine system-specific paste instructions
                                    var getSystem = '';
                                    if (navigator.userAgent.indexOf('Mac OS X') != -1) {
                                        getSystem = 'Command'
                                    } else {
                                        getSystem = 'Ctrl'
                                    }

                                    // Create and show a lightbox dialog for pasting
                                    var paDialog = elementorCommon.dialogsManager.createWidget('lightbox', {
                                        id: 'pa-paste-area-dialog',
                                        headerMessage: `${getSystem} + V`,
                                        message: container,
                                        position: {
                                            my: 'center center',
                                            at: 'center center'
                                        },
                                        onShow: function onShow() {
                                            inputArea.focus()
                                            paDialog.getElements('widgetContent').on('click', function () {
                                                inputArea.focus()
                                            });
                                        },
                                        closeButton: true,
                                        closeButtonOptions: {
                                            iconClass: 'eicon-close'
                                        },
                                    });

                                    paDialog.show();

                                }
                            } else {

                                // If Clipboard API is supported
                                navigator.clipboard.readText().then(function (pastedData) {

                                    if (PACopyPasteHandler.isValidJson(pastedData)) {

                                        var checktype = JSON.parse(pastedData);
                                        if (pastedData && typeof checktype == 'object') {
                                            // Call your paste handler function
                                            xsLocalStorage.setItem("premium-c-p-element", pastedData);

                                            PACopyPasteHandler.paste(checktype, element);
                                        }

                                    }

                                }).catch(function (err) {
                                    console.error("Error clipboard data: " + err);
                                });

                            }


                        }
                    },
                    {
                        name: "pa_copy_all",
                        title: "PA | Copy All Content",
                        callback: function () {

                            var copiedSections = Object.values(elementor.getPreviewView().children._views).map(function (e) {
                                return e.getContainer();
                            });

                            var allSections = copiedSections.map(function (e) {
                                return e.model.toJSON();
                            });

                            xsLocalStorage.setItem('', JSON.stringify(allSections), function (data) {
                                elementor.notifications.showToast({
                                    message: elementor.translate('Copied!')
                                });
                            });

                            // Create a textarea, set its value to the JSON string, and copy to clipboard
                            var textarea = document.createElement('textarea');
                            textarea.value = JSON.stringify(allSections);
                            document.body.appendChild(textarea);
                            textarea.select();
                            document.execCommand('copy');
                            document.body.removeChild(textarea);
                        }
                    },
                    {
                        name: "pa_paste_all",
                        title: "PA | Paste All Content",
                        callback: function () {

                            var editorView = elementor.$previewContents.find("html");

                            if (!navigator.clipboard.readText) {
                                // If the Clipboard API is not supported
                                var existingDialog = document.getElementById('pa-paste-area-dialog');
                                if (existingDialog) {
                                    existingDialog.parentNode.removeChild(existingDialog);
                                }

                                var paPasteElem = document.querySelector('#pa-paste-input');

                                if (!paPasteElem) {
                                    // Create a dialog for paste area
                                    var container = document.createElement('div'),
                                        paragraph = document.createElement('p');
                                    paragraph.innerHTML = 'Please grant clipboard permission for this site to paste.';

                                    var inputArea = document.createElement('input');
                                    inputArea.id = 'pa-paste-input';
                                    inputArea.type = 'text';
                                    inputArea.setAttribute('autocomplete', 'off');
                                    inputArea.setAttribute('autofocus', 'autofocus');
                                    inputArea.focus();

                                    container.appendChild(paragraph);
                                    container.appendChild(inputArea);

                                    // Handle paste event in the input area
                                    inputArea.addEventListener('paste', async function (event) {
                                        event.preventDefault();
                                        var pastedData = event.clipboardData.getData("text");

                                        if (PACopyPasteHandler.isValidJson(pastedData)) {
                                            var checktype = JSON.parse(pastedData);
                                            if (pastedData && typeof checktype == 'object') {
                                                // Call your paste handler function
                                                xsLocalStorage.setItem("premium-c-p-all", pastedData);
                                                PACopyPasteHandler.getData(checktype, element);
                                            }

                                        }

                                        var existingDialog = document.getElementById('pa-paste-area-dialog');
                                        if (existingDialog) {
                                            existingDialog.parentNode.removeChild(existingDialog);
                                        }
                                    });

                                    // Determine system-specific paste instructions
                                    let getSystem = '';
                                    if (navigator.userAgent.indexOf('Mac OS X') != -1) {
                                        getSystem = 'Command'
                                    } else {
                                        getSystem = 'Ctrl'
                                    }

                                    // Create and show a lightbox dialog for pasting
                                    var paDialog = elementorCommon.dialogsManager.createWidget('lightbox', {
                                        id: 'pa-paste-area-dialog',
                                        headerMessage: `${getSystem} + V`,
                                        message: container,
                                        position: {
                                            my: 'center center',
                                            at: 'center center'
                                        },
                                        onShow: function onShow() {
                                            inputArea.focus()
                                            paDialog.getElements('widgetContent').on('click', function () {
                                                inputArea.focus()
                                            });
                                        },
                                        closeButton: true,
                                        closeButtonOptions: {
                                            iconClass: 'eicon-close'
                                        },
                                    });

                                    paDialog.show();
                                }
                            } else {
                                navigator.clipboard.readText().then(function (pastedData) {
                                    if (PACopyPasteHandler.isValidJson(pastedData)) {
                                        var checktype = JSON.parse(pastedData);
                                        if (pastedData && typeof checktype == 'object') {
                                            // Call your paste handler function
                                            xsLocalStorage.setItem("premium-c-p-all", pastedData);

                                            PACopyPasteHandler.getData(checktype, editorView);
                                        }
                                    }
                                }).catch(function (err) {
                                    console.error("Error clipboard data: " + err);
                                });
                            }



                        }
                    },
                    ]
                })

                return groups;
            })
        })
    });




})(jQuery);