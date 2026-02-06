$(document).ready(function () {
    var passwordCache = {}; // Cache passwords for session

    // Intercept clicks on image_action links
    $(document).on('click', 'a[href*="/image_action/"]', function (e) {
        e.preventDefault();

        var url = $(this).attr('href');
        var token = url.split('/').pop();

        // Check if file needs password
        checkAndLoadFile(token);
    });

    function checkAndLoadFile(token) {
        var password = passwordCache[token] || '';

        $.ajax({
            url: viewUrl,
            type: 'POST',
            data: {
                token: token,
                password: password
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.status) {
                    // File loaded successfully
                    openInGLightbox(response);
                } else if (response.message === 'File is password protected' || response.message === 'Please enter valid password') {
                    // Need password or wrong password - clear cache and prompt again
                    delete passwordCache[token];
                    promptForPassword(token, response.message === 'Please enter valid password');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                toastr.error('Error loading file');
            }
        });
    }

    function promptForPassword(token, isRetry) {
        Swal.fire({
            title: isRetry ? 'Invalid Password' : 'Password Protected',
            text: isRetry ? 'The password you entered is incorrect. Please try again.' : 'This file is password protected',
            input: 'password',
            inputPlaceholder: 'Enter password',
            showCancelButton: true,
            confirmButtonText: 'Submit',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value) {
                    return 'Please enter a password';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                passwordCache[token] = result.value;
                checkAndLoadFile(token);
            }
        });
    }

    function openInGLightbox(fileData) {
        if (fileData.fileType === 'pdf') {
            openPdfModal(fileData);
            return;
        } else if (fileData.fileType === 'audio') {
            openAudioModal(fileData);
            return;
        } else if (fileData.fileType !== 'image' && fileData.fileType !== 'video') {
            openDownloadModal(fileData);
            return;
        }

        if (typeof GLightbox === 'undefined') {
            console.error('GLightbox not loaded, falling back to modal');
            openPdfModal(fileData);
            return;
        }

        var slides = [];
        if (fileData.fileType === 'image') {
            slides.push({
                href: fileData.filePath,
                type: 'image',
                title: fileData.fileName
            });
        } else if (fileData.fileType === 'video') {
            slides.push({
                href: fileData.filePath,
                type: 'video',
                source: 'local',
                title: fileData.fileName
            });
        }

        var glightboxInstance = GLightbox({
            elements: slides,
            touchNavigation: true,
            loop: false,
            zoomable: true,
            draggable: true,
            autofocusVideos: false
        });

        document.activeElement.blur();
        glightboxInstance.open();
    }

    function openPdfModal(fileData) {
        var modalHtml = `
            <div class="modal fade" id="pdfViewerModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" style="max-width: 95%; height: 95vh;">
                    <div class="modal-content" style="height: 100%;">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">${fileData.fileName}</h4>
                        </div>
                        <div class="modal-body" style="height: calc(100% - 120px); padding: 0;">
                            <iframe src="${fileData.filePath}" style="width:100%; height:100%; border:none;"></iframe>
                        </div>
                        <div class="modal-footer">
                            <a href="${fileData.filePath}" download="${fileData.fileName}" class="btn btn-success">
                                <i class="fa fa-download"></i> Download
                            </a>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        $('#pdfViewerModal').remove();

        // Add and show modal
        $('body').append(modalHtml);
        $('#pdfViewerModal').modal('show');

        // Clean up after closing
        $('#pdfViewerModal').on('hidden.bs.modal', function () {
            $(this).remove();
        });
    }

    function openAudioModal(fileData) {
        var modalHtml = `
            <div class="modal fade" id="audioViewerModal" tabindex="-1" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">${fileData.fileName}</h4>
                        </div>
                        <div class="modal-body" style="text-align: center; padding: 40px;">
                            <i class="fa fa-music fa-5x" style="color: #5cb85c; margin-bottom: 20px;"></i>
                            <audio controls style="width: 100%;">
                                <source src="${fileData.filePath}" type="audio/${fileData.extension}">
                                Your browser does not support the audio tag.
                            </audio>
                        </div>
                        <div class="modal-footer">
                            <a href="${fileData.filePath}" download="${fileData.fileName}" class="btn btn-success">
                                <i class="fa fa-download"></i> Download
                            </a>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#audioViewerModal').remove();
        $('body').append(modalHtml);
        $('#audioViewerModal').modal('show');

        $('#audioViewerModal').on('hidden.bs.modal', function () {
            $(this).remove();
        });
    }

    function openDownloadModal(fileData) {
        var modalHtml = `
            <div class="modal fade" id="downloadModal" tabindex="-1" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">${fileData.fileName}</h4>
                        </div>
                        <div class="modal-body" style="text-align: center; padding: 40px;">
                            <i class="fa fa-file fa-5x" style="color: #337ab7; margin-bottom: 20px;"></i>
                            <p>Preview not available for this file type.</p>
                            <p><strong>File Type:</strong> ${fileData.extension.toUpperCase()}</p>
                        </div>
                        <div class="modal-footer">
                            <a href="${fileData.filePath}" target="_blank" class="btn btn-primary" style="margin-top: 0px;">
                                <i class="fa fa-external-link"></i> Open in New Tab
                            </a>
                            <a href="${fileData.filePath}" download="${fileData.fileName}" class="btn btn-success">
                                <i class="fa fa-download"></i> Download
                            </a>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#downloadModal').remove();
        $('body').append(modalHtml);
        $('#downloadModal').modal('show');

        $('#downloadModal').on('hidden.bs.modal', function () {
            $(this).remove();
        });
    }
});
