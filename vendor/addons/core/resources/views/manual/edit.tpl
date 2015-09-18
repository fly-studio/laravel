<{extends file="extends/main.block.tpl"}>

<{block "head-scripts-plus"}>
<script>var $ = jQuery;</script>
<script src="<{'static/js/editor.md/editormd.min.js'|url}>"></script>
<{/block}>

<{block "head-styles-plus"}>
<link rel="stylesheet" href="<{'static/js/editor.md/css/editormd.min.css'|url}>" />
<{/block}>

<{block "body-container"}>
<div class="container">
	<h1 class="page-header">编辑</h1>
	<form action="<{'manual'|url}>/<{$_data->id}>" id="form" method="POST" class="form-horizontal form-bordered">
		<{csrf_field() nofilter}>
		<{method_field('PUT') nofilter}>
		<{include file="manual/fields.inc.tpl"}>
	</form>
</div>
<{/block}>

<{block "body-scripts"}>
<script>
(function($){
	//editormd.urls.atLinkBase = $.baseuri + 'member/';
	editormd('editormd', {
		width   : '100%',
		height  : 640,
		syncScrolling : 'single',
		path    : $.baseuri + 'static/js/editor.md/lib/',
		//theme : "dark",
		//previewTheme : "dark",
		//editorTheme : "pastel-on-dark",
		//markdown : md,
		codeFold : true,
		//syncScrolling : false,
		//saveHTMLToTextarea : true,    // 保存 HTML 到 Textarea
		searchReplace : true,
		//watch : false,                // 关闭实时预览
		htmlDecode : "style,script,iframe|on*",            // 开启 HTML 标签解析，为了安全性，默认不开启    
		//toolbar  : false,             //关闭工具栏
		//previewCodeHighlight : false, // 关闭预览 HTML 的代码块高亮，默认开启
		atLink    : false,    // enable @link
		//emailLink : true,    // enable email address auto link
		emoji : true,
		taskList : true,
		tocm            : true,         // Using [TOCM]
		tex : true,                   // 开启科学公式TeX语言支持，默认关闭
		flowChart : true,             // 开启流程图支持，默认关闭
		sequenceDiagram : true,       // 开启时序/序列图支持，默认关闭,
		//dialogLockScreen : false,   // 设置弹出层对话框不锁屏，全局通用，默认为true
		//dialogShowMask : false,     // 设置弹出层对话框显示透明遮罩层，全局通用，默认为true
		//dialogDraggable : false,    // 设置弹出层对话框不可拖动，全局通用，默认为true
		//dialogMaskOpacity : 0.4,    // 设置透明遮罩层的透明度，全局通用，默认值为0.1
		//dialogMaskBgColor : "#000", // 设置透明遮罩层的背景颜色，全局通用，默认为#fff
		imageUpload : true,
		imageFormats : ["jpg", "jpeg", "gif", "png", "bmp", "webp"],
		imageUploadURL : $.baseuri+"attachment/editormd_upload_query?of=json&_token=" + $.csrf,
		onload : function() {
			//console.log('onload', this);
			//this.fullscreen();
			//this.unwatch();
			//this.watch().fullscreen();

			//this.setMarkdown("#PHP");
			//this.width("100%");
			//this.height(480);
			//this.resize("100%", 640);
		}

	});
<{call validate selector='#form'}>
})(jQuery);
</script>
<{/block}>