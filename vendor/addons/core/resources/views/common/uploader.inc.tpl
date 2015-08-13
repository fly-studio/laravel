<!--本文件需要放置在common.js之后-->
<script src="<{'static/js/swfupload/swfupload.js'|url}>"></script>
<style type="text/css">
.upload-preview {position: relative;}
.upload-preview img {border:1px solid #000;}
.upload-preview .actions {position: absolute; width: 50px; height: 28px;
	background-color: #000000; padding: 5px 8px; border-radius: 5px; top: 50%; left: 50%; margin-top: -13px; margin-left: -24px; opacity: 0; -moz-transition: opacity 0.3s ease-in-out;}
.upload-preview.active .actions, .upload-preview:hover .actions {opacity: 0.8;}

.progressWrapper {width: 357px; overflow: hidden; }
.progressContainer {margin: 5px; padding: 4px; border: solid 1px #E8E8E8; background-color: #F7F7F7; overflow: hidden; } /* Message */
.progressWrapper .message {margin: 1em 0; padding: 10px 20px; border: solid 1px #FFDD99; background-color: #FFFFCC; overflow: hidden; } /* Error */
.progressWrapper .red {border: solid 1px #B50000; background-color: #FFEBEB; }
/* Current */
.progressWrapper .green {border: solid 1px #DDF0DD; background-color: #EBFFEB; }
/* Complete */
.progressWrapper .blue {border: solid 1px #CEE2F2; background-color: #F0F5FF; }
.progressWrapper .progressName {font-size: 12px; font-weight: 700; color: #555; width: 323px; height: 16px; text-align: left; white-space: nowrap; overflow: hidden; }
.progressWrapper .progressBarInProgress,
.progressWrapper .progressBarComplete,
.progressWrapper .progressBarError {font-size: 0; width: 0%; height: 2px; background-color: blue; margin-top: 2px; }
.progressWrapper .progressBarComplete {width: 100%; background-color: green; }
.progressWrapper .progressBarError {width: 100%; background-color: red; }
.progressWrapper .progressBarStatus {margin-top: 2px; width: 337px; font-size: 7pt; font-family: Arial; text-align: left; white-space: nowrap; }
.progressWrapper a.progressCancel {font-size: 0; display: block; height: 14px; width: 14px; background-image: url('<{'static/js/swfupload/img/cancelbutton.gif'|url}>'); background-repeat: no-repeat; background-position: -14px 0px; float: right; }
.progressWrapper a.progressCancel:hover {background-position: 0px 0px; }

/* -- SWFUpload Object Styles ------------------------------- */
.swfupload {
	vertical-align: top;
}
</style>
<script src="<{'static/js/swfupload/jquery.upload-file.js'|url}>?session_id=<{''|@session_id|encrypt|escape:'url'}>"></script>
<script type="text/javascript">
(function($){
	$.fn.extend({uploader: $.fn.upload_file});
})(jQuery);
</script>
