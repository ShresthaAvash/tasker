<style>
/* New Modern Tab Styles for Edit Page */
.card-body > .nav-tabs {
border-bottom: 1px solid #dee2e6;
position: relative;
margin-bottom: 1.5rem; /* Add some space below the tabs */
}
.card-body > .nav-tabs .nav-item .nav-link {
border: none !important;
color: #6c757d !important; /* A slightly lighter, more modern grey */
font-weight: 600;
padding: 1rem 1.25rem;
position: relative;
transition: color 0.2s ease-in-out;
margin-bottom: -1px; /* Overlap the main border */
}
.card-body > .nav-tabs .nav-item .nav-link:hover {
color: #0c6ffd !important;
background-color: transparent !important;
}
.card-body > .nav-tabs .nav-item .nav-link.active {
color: #0c6ffd !important;
background-color: transparent !important;
}
.card-body > .nav-tabs .nav-item .nav-link::after {
content: '';
position: absolute;
bottom: 0;
left: 50%;
transform: translateX(-50%);
width: 0;
height: 3px;
background-color: #0c6ffd;
transition: width 0.3s ease, left 0.3s ease, transform 0.3s ease;
}
.card-body > .nav-tabs .nav-item .nav-link.active::after,
.card-body > .nav-tabs .nav-item .nav-link:hover::after {
width: 100%;
left: 0;
transform: translateX(0);
}

/* Pinned Note Styles */
.pinned-note-bar {
background-color: #e3f2fd; /* Light blue background */
border: 1px solid #b8daff; /* Slightly darker blue border */
border-left: 5px solid #0c6ffd; /* Accent color left border */
border-radius: .375rem;
padding: 1rem 1.25rem;
margin-bottom: 1.5rem;
position: relative;
}
.pinned-note-bar .unpin-btn {
position: absolute;
top: 0.5rem;
right: 0.5rem;
}

/* Select2 Styles */
.select2-container--default .select2-selection--multiple { background-color: #fff; border-color: #ced4da; color: #495057; }
.select2-container--default .select2-selection--multiple .select2-selection__choice { background-color: #0c6ffd; border-color: #0a58ca; color: #fff; }
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove { color: rgba(255,255,255,0.7); }
.select2-container--default .select2-search--inline .select2-search__field { color: #495057; }
.select2-dropdown { border-color: #ced4da; }

/* Create Service Modal Styles */
#createServiceModal .job-block { border: 1px solid #e9ecef; border-radius: 5px; margin-bottom: 1rem; }
#createServiceModal .job-header { background-color: #f8f9fa; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e9ecef; }
#createServiceModal .task-item { border-top: 1px solid #f1f1f1; padding: 0.75rem 1.25rem; }

.collapsing { transition: height 0.35s cubic-bezier(0.4, 0, 0.2, 1); }
a[data-toggle="collapse"] .collapse-icon { transition: transform 0.3s ease-in-out; }
a[data-toggle="collapse"][aria-expanded="true"] .collapse-icon { transform: rotate(-180deg); }
.custom-control-label::before, .custom-control-label::after { cursor: pointer; }

/* Accordion Header Hover Effects */
.service-header-link:hover { background-color: #d1ecf1 !important; }
.job-header-link:hover { background-color: #e9ecef !important; }

/* Modern Modal Styles */
#documentUploadModal .modal-header { background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; }
.file-drop-zone { border: 2px dashed #adb5bd; border-radius: .375rem; padding: 2.5rem 1rem; text-align: center; background-color: #f8f9fa; cursor: pointer; transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out; }
.file-drop-zone.is-active { background-color: #e9ecef; border-color: #0c6ffd; }
.file-drop-zone .file-drop-icon { font-size: 2.5rem; color: #6c757d; }
.file-drop-zone .file-drop-text { color: #495057; margin-top: 0.5rem; }
#file-preview-list { display: none; max-height: 200px; overflow-y: auto; margin-top: 1rem; }
.file-preview-item { display: flex; align-items: center; background-color: #e9ecef; border-radius: .375rem; padding: 0.75rem; margin-bottom: 0.5rem; animation: fadeInUp 0.5s ease forwards; opacity: 0; }
.file-preview-icon { font-size: 2rem; margin-right: 1rem; flex-shrink: 0; width: 30px; text-align: center; }
.file-preview-info { text-align: left; flex-grow: 1; overflow: hidden; }
.file-preview-name { font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.file-preview-size { font-size: 0.8rem; color: #6c757d; }
.remove-file-btn { font-size: 1.2rem; line-height: 1; color: #dc3545; background: none; border: none; padding: 0 .5rem; cursor: pointer; }

.file-preview-item.removing { animation: fadeOutUp 0.3s ease forwards; }

@keyframes fadeInUp {
from { opacity: 0; transform: translateY(10px); }
to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeOutUp {
from { opacity: 1; transform: translateY(0); }
to { opacity: 0; transform: translateY(-10px); }
}

/* --- THIS IS THE FIX FOR THE BUTTON COLOR --- */
#next-to-jobs-btn {
background-color: #007afe !important;
border-color: #007afe !important;
color: #fff;
}

#next-to-jobs-btn:hover {
background-color: #0069d9 !important;
border-color: #0062cc !important;
}

/* --- NEW STYLES FOR NOTES & COMMENTS MODAL --- */
#notes-comments-list { max-height: 400px; overflow-y: auto; padding: 5px; }
.comment-item { display: flex; margin-bottom: 1.25rem; max-width: 85%; animation: fadeInUp 0.4s ease forwards; }
.comment-item.is-author { margin-left: auto; flex-direction: row-reverse; }
.comment-author-avatar { flex-shrink: 0; width: 40px; height: 40px; background-color: #6c757d; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: bold; margin: 0 10px; }
.comment-item.is-author .comment-author-avatar { background-color: #007bff; }
.comment-body { background-color: #f1f3f5; border-radius: 12px; padding: 0.75rem 1rem; width: 100%; }
.comment-item.is-author .comment-body { background-color: #e7f5ff; }
.comment-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem; }
.comment-author-name { font-weight: 600; font-size: 0.9rem; }
.comment-timestamp { font-size: 0.75rem; color: #6c757d; }
.comment-content p { margin: 0; white-space: pre-wrap; word-wrap: break-word; font-size: 0.95rem; }
.comment-actions { margin-top: 0.5rem; text-align: right; }
.comment-edit-form { display: none; }
</style>