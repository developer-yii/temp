$(document).ready(function () {

    var table = $('#data-table').DataTable({
        stripeClasses: [],
        processing: true,
        serverSide: false,
        ajax: listUrl,
        rowId: function (row) { return 'row-' + row.id; },
        order: [[0, 'desc']],
        columns: [
            {
                data: 'created_at',
                name: 'created_at',
                visible: false
            },
            {
                data: 'delivery_message',
                name: 'delivery_message',
                render: function (data, type, full, meta) {
                    return renderContentWithReadMoreAndLess(data, 'message-' + full.id, full.sender_email);
                }
            },
            {
                data: 'images',
                name: 'images',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (data, type, full, meta) {
                    if (data && data.length > 0) {
                        let html = '<div class="notification-gallery" id="notification-gallery-' + full.id + '">';
                        data.forEach(function (image) {
                            let imageUrl = storageUrl + '/delivery_images/' + image.image_path;
                            let isPdf = image.image_path.toLowerCase().endsWith('.pdf');
                            
                            let thumbnailHtml = isPdf 
                                ? `<div style="height: 60px; width: 60px; display: flex; flex-direction: column; align-items: center; justify-content: center; background-color: #f8f9fa;">
                                        <i class="fa fa-file-pdf-o text-danger" style="font-size: 36px; line-height: 1;"></i>
                                   </div>`
                                : `<img src="${imageUrl}" style="height: 60px; width: 60px; object-fit: cover; display:block;">`;

                            html += `<a href="${imageUrl}" class="glightbox" data-gallery="gallery-${full.id}" style="display: inline-block; margin: 4px; padding: 4px; border: 1px solid #ddd; border-radius: 4px; background: #fff; text-decoration: none; transition: opacity 0.2s;" onmouseover="this.style.opacity=0.7" onmouseout="this.style.opacity=1">
                                        ${thumbnailHtml}
                                     </a>`;
                        });
                        html += '</div>';
                        return html;
                    }
                    return '';
                }
            },
        ],
        createdRow: function (row, data) {
            if (data.is_read == 0) {
                $(row).addClass('unread-row');
            }
        },
        initComplete: function () {
            var api = this.api();

            // Initialize GLightbox for notification galleries
            initNotificationGalleries();

            var unreadIds = [];
            api.rows().data().each(function (d) {
                if (d.is_read == 0) {
                    unreadIds.push(d.id);
                }
            });

            if (unreadIds.length) {
                setTimeout(function () {
                    $.ajax({
                        url: markAsReadUrl,
                        type: 'POST',
                        data: {
                            ids: unreadIds,
                            type: 'notification',
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function () {
                            var $badge = $('a[href*="notifications"]').find('.badge');
                            var currentCount = parseInt($badge.text()) || 0;
                            var newCount = currentCount - unreadIds.length;
                            if (newCount <= 0) {
                                $badge.hide();
                                $('a[href*="notifications"]').removeClass('notification-btn');
                            } else {
                                $badge.text(newCount);
                            }
                            unreadIds.forEach(function (id) {
                                var row = api.row('#row-' + id).node();
                                if (row) {
                                    $(row).removeClass('unread-row');
                                }
                            });
                        }
                    });
                }, 700);
            }
        }
    });

    function renderContentWithReadMoreAndLess(content, elementId, senderName = null) {
        var shortText = content ? content.replace(/\n/g, '<br>').substring(0, 100) : '';
        var fullText = content ? content.replace(/\n/g, '<br>') : '';
        var sentBy = senderName ? '<div style="text-align: right; font-style: italic; margin-top: 5px; color:blue;">Sent By: ' + senderName + '</div>' : '';

        if (content && content.length > 100) {
            return '<div class="file-name"><span class="short-text" id="' + elementId + '-short">' + shortText + '... ' +
                '<a href="#" class="read-more" data-id="' + elementId + '" style="font-weight: 600;">Read More</a>' + sentBy + '</span>' +
                '<span class="full-text" id="' + elementId + '" style="display:none;">' + fullText +
                ' <a href="#" class="read-less" data-id="' + elementId + '" style="font-weight: 600;">Read Less</a>' + sentBy + '</span></div>';
        }
        return fullText + sentBy;  // Return full content if less than 100 characters
    }

    $('body').on('click', '.read-more', function (e) {
        e.preventDefault();  // Prevent the default link click behavior

        var elementId = $(this).data('id');  // Get the ID of the element to display
        $('#' + elementId + '-short').hide();  // Hide the short text with "Read More"
        $('#' + elementId).show();  // Show the full text with "Read Less"
    });

    $('body').on('click', '.read-less', function (e) {
        e.preventDefault();  // Prevent the default link click behavior

        var elementId = $(this).data('id');  // Get the ID of the element to hide
        $('#' + elementId).hide();  // Hide the full text with "Read Less"
        $('#' + elementId + '-short').show();  // Show the short text with "Read More"
    });

    function initNotificationGalleries() {
        if (typeof GLightbox === 'undefined') return;

        GLightbox({
            selector: '.glightbox',
            touchNavigation: true,
            loop: true,
            zoomable: true,
            draggable: true,
            autofocusVideos: false,
            openEffect: 'fade',
            closeEffect: 'fade'
        });
    }

});
