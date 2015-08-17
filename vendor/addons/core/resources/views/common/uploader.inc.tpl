<!--本文件需要放置在common.js之后-->
<link rel="stylesheet" href="<{'static/js/webuploader/webuploader.css'|url}>" />
<script src="<{'static/js/webuploader/webuploader.nolog.min.js'|url}>"></script>
<script src="<{'static/js/mimetype.min.js'|url}>"></script>
<style>
	.uploader-container {position: relative;}
	.webuploader-dnd-over { border: 1px #ccc solid; padding: 20px;position: relative;box-shadow: 0px 0px 20px rgba(0,0,0,0.3);}
	.uploader-container .drop-tips {z-index:999999;top:20px; left:0; background-color: #fff; opacity: 0.8;filter: alpha(opacity=80);  width: 100%; height: 100%; position:absolute; display: none;text-align: center;}
	.webuploader-dnd-over .drop-tips {display: block;}
	.uploader-container .thumbnail .file-panel {
		position: absolute;height: 0;width: 100%;top: 0;left: 0;overflow: hidden;z-index: 300;
		filter: progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr='#80000000', endColorstr='#80000000')\0;background: rgba( 0, 0, 0, 0.5 );
	}
	.uploader-container .thumbnails:hover .thumbnail .file-panel {height: 30px;}
	.uploader-container .thumbnail .file-panel span {width: 24px;height: 24px;display: inline;float: right;text-indent: -9999px;overflow: hidden;background: url('<{'static/js/webuploader/icons.png'|url}>') no-repeat;margin: 5px 1px 1px;cursor: pointer;}
	.uploader-container .thumbnail .file-panel span.rotateLeft {background-position: 0 -24px;}
	.uploader-container .thumbnail .file-panel span.rotateLeft:hover {background-position: 0 0;}
	.uploader-container .thumbnail .file-panel span.rotateRight {background-position: -24px -24px;}
	.uploader-container .thumbnail .file-panel span.rotateRight:hover {background-position: -24px 0;}
	.uploader-container .thumbnail .file-panel span.cancel {background-position: -48px -24px;}
	.uploader-container .thumbnail .file-panel span.cancel:hover {background-position: -48px 0;}
	.uploader-container .thumbnails .caption .title {word-break: break-all;word-wrap: break-word;}
	.uploader-container .media .media-body .media-heading {word-break: break-all;word-wrap: break-word;}
	.uploader-container .media .media-body .media-message {word-break: break-all;word-wrap: break-word;}
</style>
<script src="<{'static/js/jquery.uploader.js'|url}>?session_id=<{''|@session_id|encrypt|escape:'url' nofilter}>"></script>
