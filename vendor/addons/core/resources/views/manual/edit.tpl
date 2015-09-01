<{extends file="extends/main.block.tpl"}>

<{block "head-scripts-plus"}>
<script src="<{'static/js/editor.md/editormd.min.js'|url}>"></script>
<{/block}>

<{block "body-container"}>
<div class="container">
	<h1 class="page-header">编辑</h1>
	<form action="<{'manual'|url}>/<{$_data->id}>" id="form" method="POST" class="form-horizontal form-bordered">
		<{csrf_field() nofilter}>
		<{method_field('PUT') nofilter}>
		<{include file="manual/fiedls.inc.tpl"}>
	</form>
</div>
<{/block}>

<{block "body-scripts"}>
<script>
(function($){
	editormd('editormd', {
		width   : '90%',
		height  : 640,
		syncScrolling : 'single',
		path    : $.baseuri + 'static/js/editor.md/lib/'
	});
})(jQuery);
</script>
<{/block}>