/**
 * Admin Page JavaScript
 * Headline Ligatures & Styles Plugin
 */

jQuery(document).ready(function($) {
    'use strict';

    // Tab switching with ARIA support
    $('.hls-tab-button').on('click', function() {
        var tab = $(this).data('tab');

        // Update ARIA states
        $('.hls-tab-button').removeClass('active').attr('aria-selected', 'false');
        $(this).addClass('active').attr('aria-selected', 'true');

        $('.hls-tab-content').removeClass('active').attr('hidden', 'true');
        var $panel = $('#hls-tab-' + tab);
        $panel.addClass('active').removeAttr('hidden');

        // Move focus to panel for screen readers
        $panel.focus();
    });

    // Add keyboard navigation (arrow keys for tabs)
    $('.hls-tab-button').on('keydown', function(e) {
        var $tabs = $('.hls-tab-button');
        var currentIndex = $tabs.index(this);
        var newIndex;

        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
            e.preventDefault();
            newIndex = (currentIndex + 1) % $tabs.length;
            $tabs.eq(newIndex).click().focus();
        } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
            e.preventDefault();
            newIndex = (currentIndex - 1 + $tabs.length) % $tabs.length;
            $tabs.eq(newIndex).click().focus();
        } else if (e.key === 'Home') {
            e.preventDefault();
            $tabs.first().click().focus();
        } else if (e.key === 'End') {
            e.preventDefault();
            $tabs.last().click().focus();
        }
    });

    // File selection handling
    var selectedFile = null;

    // Trigger file input when button is clicked
    $('#hls-select-file-btn').on('click', function() {
        $('#hls-font-file').click();
    });

    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Handle file selection
    $('#hls-font-file').on('change', function(e) {
        var file = e.target.files[0];
        if (!file) return;

        // Validate file type
        if (!file.name.endsWith('.zip')) {
            alert(hlsAdmin.strings.selectZip);
            $(this).val('');
            return;
        }

        selectedFile = file;

        // Show file name and size
        $('#hls-file-name').text(file.name);
        $('#hls-file-size').text('(' + formatFileSize(file.size) + ')');
        $('#hls-selected-file').show();

        // Enable upload button
        $('#hls-upload-font-btn').prop('disabled', false);

        // Auto-fill kit name from filename if empty
        if (!$('#hls-font-name').val()) {
            var kitName = file.name.replace(/\.(zip)$/i, '');
            $('#hls-font-name').val(kitName);
        }
    });

    // Clear file selection
    $('#hls-clear-file-btn').on('click', function() {
        selectedFile = null;
        $('#hls-font-file').val('');
        $('#hls-selected-file').hide();
        $('#hls-upload-font-btn').prop('disabled', true);
    });

    // Upload font kit
    $('#hls-upload-font-btn').on('click', function() {
        var $btn = $(this);
        var $message = $('#hls-font-message');
        var $progress = $('#hls-upload-progress');
        var $progressFill = $('.hls-progress-fill');
        var $progressText = $('.hls-progress-text');
        var $progressBar = $('.hls-progress-bar');
        var fontName = $('#hls-font-name').val().trim();

        // Clear previous message
        $message.html('');

        // Validate
        if (!fontName) {
            $message.html('<div class="notice notice-error inline"><p>' + hlsAdmin.strings.enterName + '</p></div>');
            $('#hls-font-name').focus().attr('aria-invalid', 'true');
            return;
        }

        if (!selectedFile) {
            $message.html('<div class="notice notice-error inline"><p>' + hlsAdmin.strings.selectFile + '</p></div>');
            return;
        }

        // Clear aria-invalid on success
        $('#hls-font-name').attr('aria-invalid', 'false');

        // Prepare FormData
        var formData = new FormData();
        formData.append('zip_file', selectedFile);
        formData.append('name', fontName);

        // Disable button, show progress, and add aria-busy
        $('.hls-upload-form').attr('aria-busy', 'true');
        $btn.prop('disabled', true).text(hlsAdmin.strings.uploading);
        $progress.show();
        $progressFill.css('width', '0%');
        $progressText.text(hlsAdmin.strings.uploadingZip);
        $progressBar.attr('aria-valuenow', '0');

        // Upload via REST API
        $.ajax({
            url: hlsAdmin.restUrl + 'fonts',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', hlsAdmin.nonce);
            },
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                // Upload progress
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        var percentComplete = Math.round((e.loaded / e.total) * 100);
                        $progressFill.css('width', percentComplete + '%');
                        $progressBar.attr('aria-valuenow', percentComplete);
                        $progressText.text(hlsAdmin.strings.uploading + ' ' + percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                $progressText.text(hlsAdmin.strings.processing);
                $progressFill.css('width', '100%');
                $progressBar.attr('aria-valuenow', '100');

                $message.html('<div class="notice notice-success inline"><p>' + hlsAdmin.strings.uploadSuccess + '</p></div>');

                // Reset form
                selectedFile = null;
                $('#hls-font-name').val('');
                $('#hls-font-file').val('');
                $('#hls-selected-file').hide();

                // Refresh page after 2 seconds
                setTimeout(function() {
                    location.reload();
                }, 2000);
            },
            error: function(xhr) {
                var errorMsg = hlsAdmin.strings.uploadError;
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                $message.html('<div class="notice notice-error inline"><p>' + errorMsg + '</p></div>');
                $progress.hide();
            },
            complete: function() {
                $('.hls-upload-form').attr('aria-busy', 'false');
                $btn.prop('disabled', false).text(hlsAdmin.strings.uploadButton);
            }
        });
    });

    // Font preview selector
    $('#hls-preview-font-select').on('change', function() {
        var selectedFont = $(this).val();

        // Update all feature demo previews
        $('.hls-feature-preview').each(function() {
            if (selectedFont) {
                $(this).css('font-family', selectedFont);
            } else {
                $(this).css('font-family', 'Georgia, serif');
            }
        });

        // Update all preset previews (if any exist)
        $('.hls-preset-preview').each(function() {
            if (selectedFont) {
                $(this).css('font-family', selectedFont);
            } else {
                $(this).css('font-family', 'Georgia, serif');
            }
        });
    });

    // Preview size slider
    $('#hls-preview-size-slider').on('input', function() {
        var size = $(this).val();
        var $slider = $(this);

        // Update the displayed value
        $('#hls-preview-size-value').text(size + 'px');

        // Update ARIA attributes
        $slider.attr('aria-valuenow', size);
        $slider.attr('aria-valuetext', size + ' pixels');

        // Update all feature demo previews
        $('.hls-feature-preview').css('font-size', size + 'px');

        // Update all preset previews (if any exist)
        $('.hls-preset-preview').css('font-size', size + 'px');
    });

    // Delete font
    $('.hls-delete-font').on('click', function() {
        if (!confirm(hlsAdmin.strings.confirmDelete)) {
            return;
        }

        var $btn = $(this);
        var fontId = $btn.data('font-id');

        $btn.prop('disabled', true);

        $.ajax({
            url: hlsAdmin.restUrl + 'fonts/' + fontId,
            method: 'DELETE',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', hlsAdmin.nonce);
            },
            success: function() {
                $btn.closest('.hls-font-card').fadeOut(function() {
                    $(this).remove();
                    if ($('.hls-font-card').length === 0) {
                        $('.hls-fonts-list').replaceWith('<div class="hls-empty-state" role="status"><p><strong>' + hlsAdmin.strings.noFonts + '</strong></p><p>' + hlsAdmin.strings.uploadPrompt + '</p></div>');
                    }
                });
            },
            error: function() {
                alert(hlsAdmin.strings.deleteError);
                $btn.prop('disabled', false);
            }
        });
    });
});
