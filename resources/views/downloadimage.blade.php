@php
    $userid = Auth::user()->id;
@endphp
@extends('layouts.app')

@section('content')
<style>
    .file-viewer-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .file-header {
        background: #fcfcfc;
        color: #000;
        padding: 25px 30px;
        border-radius: 8px 8px 0 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: 1px solid #ddd;
        border-bottom: none;
    }
    
    .file-title {
        font-size: 22px;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #000;
    }
    
    .file-title i {
        font-size: 24px;
    }
    
    .file-actions {
        background: #fcfcfc;
        padding: 20px;
        border-left: 1px solid #ddd;
        border-right: 1px solid #ddd;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }
    
    .file-actions .btn-group {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .btn-modern {
        padding: 12px 30px;
        font-size: 16px;
        font-weight: 500;
        border-radius: 5px;
        border: 1px solid #ccc;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none !important;
    }
    
    .btn-view {
        background: #337ab7;
        color: #fff !important;
        border-color: #2e6da4;
    }
    
    .btn-view:hover {
        background: #286090;
        border-color: #204d74;
        color: #fff !important;
    }
    
    .btn-download {
        background: #5cb85c;
        color: #fff !important;
        border-color: #4cae4c;
    }
    
    .btn-download:hover {
        background: #449d44;
        border-color: #398439;
        color: #fff !important;
    }
    
    .password-section {
        background: #fcf8e3;
        border: 1px solid #faebcc;
        border-radius: 5px;
        padding: 20px 30px;
        margin-bottom: 5px;
        text-align: center;
        width: 100%;
        max-width: 450px;
    }
    
    .password-section input {
        border: 1px solid #ccc;
        border-radius: 4px;
        padding: 10px 15px;
        font-size: 16px;
        width: 100%;
        max-width: 400px;
        background: #fff;
        color: #000;
    }
    
    .preview-container {
        background: #fff;
        padding: 0;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
        border: 1px solid #ddd;
        border-top: none;
    }
    
    .preview-content {
        min-height: 600px;
        background: #fff;
    }
    
    .preview-content iframe {
        width: 100%;
        height: 800px;
        border: none;
        background: #fff;
    }
    
    .preview-content img {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 0 auto;
    }
    
    .preview-content video,
    .preview-content audio {
        max-width: 100%;
        display: block;
        margin: 20px auto;
    }
    
    .loading-spinner {
        text-align: center;
        padding: 60px;
        color: #000;
    }
    
    .loading-spinner i {
        font-size: 48px;
        animation: spin 1s linear infinite;
        color: #337ab7;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .image-preview-section {
        background: #fff;
        padding: 30px;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: 1px solid #ddd;
        border-top: none;
    }
    
    .image-preview-section img {
        border-radius: 5px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .no-file-container {
        text-align: center;
        padding: 80px 20px;
        background: #fcfcfc;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: 1px solid #ddd;
    }
    
    .no-file-container i {
        font-size: 80px;
        color: #d9534f;
        margin-bottom: 20px;
    }
    
    @media (max-width: 768px) {
        .file-header {
            padding: 20px;
        }
        
        .file-title {
            font-size: 18px;
        }
        
        .btn-modern {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="file-viewer-container">
    @if($image)
        <input type="hidden" value="{{ $image->id}}" id="file-id">
        
        <div class="file-header">
            <h1 class="file-title">
                <i class="fa fa-file"></i>
                {{ $image->file_name}}
            </h1>
        </div>
        
        <div class="file-actions">
            @if(!empty($image->password))
            <div class="password-section">
                <i class="fa fa-lock" style="font-size: 28px; color: #8a6d3b; margin-bottom: 10px; display: block;"></i>
                <h4 style="margin-bottom: 10px; color: #8a6d3b;">🔒 Password Protected File</h4>
                <input type="password" id="file-password" class="form-control" placeholder="Enter password to view this file" style="margin-bottom: 8px;">
                <p style="margin: 0; color: #8a6d3b; font-size: 13px;"><strong>This file requires a password to view</strong></p>
            </div>
            @endif
            <div class="btn-group">
                <button type="button" class="btn btn-modern btn-view" id="view-file-btn">
                    <i class="fa fa-eye"></i> View Online
                </button>
                <button type="button" class="btn btn-modern btn-download" id="download-file-btn">
                    <i class="fa fa-download"></i> Download File
                </button>
            </div>
        </div>

        <!-- File Preview Area -->
        <div id="file-preview" style="display: none;">
            <div class="preview-container">
                <div class="preview-content" id="preview-content">
                    <div class="loading-spinner">
                        <i class="fa fa-spinner fa-spin"></i>
                        <p>Loading file...</p>
                    </div>
                </div>
            </div>
        </div>


    @else
        <div class="no-file-container">
            <i class="fa fa-exclamation-triangle"></i>
            <h2 style="color: #d9534f; margin-bottom: 10px;">File Not Found</h2>
            <p style="color: #000; font-size: 16px;">The file you're looking for doesn't exist or has been deleted.</p>
            <a href="{{ route('home') }}" class="btn btn-modern btn-view" style="margin-top: 20px;">
                <i class="fa fa-home"></i> Go to Home
            </a>
        </div>
    @endif
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    var viewUrl = "{{ route('image.view') }}";
    var downloadUrl = "{{ route('image.download') }}";

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Auto-click view button on page load (only if no password)
    @if(empty($image->password))
    setTimeout(function() {
        $('#view-file-btn').click();
    }, 300);
    @endif

    // View File
    $('#view-file-btn').click(function() {
        var fileId = $('#file-id').val();
        var password = $('#file-password').val();
        
        $('#file-preview').show();
        $('#preview-content').html('<div class="loading-spinner"><i class="fa fa-spinner fa-spin"></i><p>Loading file...</p></div>');

        $.ajax({
            url: viewUrl,
            type: "POST",
            data: { id: fileId, password: password },
            success: function(response) {
                if (response.status) {
                    showPreview(response);
                    toastr.success('File loaded successfully!');
                } else {
                    $('#preview-content').html('<div style="text-align:center; padding:60px; color:#d9534f;"><i class="fa fa-exclamation-circle" style="font-size:48px; margin-bottom:20px;"></i><h4>' + response.message + '</h4></div>');
                    toastr.error(response.message);
                }
            },
            error: function() {
                $('#preview-content').html('<div style="text-align:center; padding:60px; color:#d9534f;"><i class="fa fa-exclamation-circle" style="font-size:48px; margin-bottom:20px;"></i><h4>Error loading file</h4></div>');
                toastr.error('Error loading file');
            }
        });
    });

    // Download File
    $('#download-file-btn').click(function() {
        var fileId = $('#file-id').val();
        var password = $('#file-password').val();

        $.ajax({
            url: downloadUrl,
            type: "POST",
            data: { id: fileId, password: password },
            success: function(response) {
                if (response.imagePath) {
                    var link = document.createElement('a');
                    link.href = response.imagePath;
                    link.download = response.imagename;
                    link.click();
                    toastr.success('Download started!');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Error downloading file');
            }
        });
    });

    function showPreview(data) {
        var content = '';

        if (data.fileType === 'pdf') {
            content = '<iframe src="' + data.filePath + '" style="width:100%; height:800px; border:none;"></iframe>';
        } else if (data.fileType === 'image') {
            content = '<div style="text-align:center; padding:30px; background:#fff;"><img src="' + data.filePath + '" style="max-width:100%; height:auto; border-radius:5px; box-shadow:0 2px 8px rgba(0,0,0,0.1);"></div>';
        } else if (data.fileType === 'video') {
            content = '<div style="text-align:center; padding:30px; background:#fff;"><video controls style="max-width:100%; border-radius:5px; box-shadow:0 2px 8px rgba(0,0,0,0.1);"><source src="' + data.filePath + '"></video></div>';
        } else if (data.fileType === 'audio') {
            content = '<div style="text-align:center; padding:60px; background:#fff;"><i class="fa fa-music" style="font-size:80px; color:#337ab7; margin-bottom:30px;"></i><br><audio controls style="width:100%; max-width:600px;"><source src="' + data.filePath + '"></audio></div>';
        } else {
            content = '<div style="text-align:center; padding:60px; background:#fff;"><i class="fa fa-file" style="font-size:80px; color:#000; margin-bottom:20px;"></i><p style="font-size:18px; color:#000;">Preview not available for this file type</p><a href="' + data.filePath + '" target="_blank" class="btn btn-modern btn-view" style="margin-top:20px;"><i class="fa fa-external-link"></i> Open in New Tab</a></div>';
        }

        $('#preview-content').html(content);
        $('html, body').animate({ scrollTop: $('#file-preview').offset().top - 20 }, 500);
    }
});
</script>
@endsection
