<{extends file="extends/main.block.tpl"}>

<{block "head-styles-plus"}>
<link rel="stylesheet" href="<{'static/css/font-awesome.min.css'|url}>" />
<style>
	.media-body-container {height:250px; overflow: hidden; position: relative;}
	.media-body-expand-btn {display: none; position: absolute; bottom: 0px; height:20px; left : 10px; right: 10px;line-height: 15px;}
</style>
<{/block}>



<{block "body-container"}>
<div class="container">
	<h1 class="page-header">手册列表</h1>
	<div class="row">
		<{foreach $_menu as $v}>
		<div class="media">
			<div class="media-left">
				<i class="glyphicon glyphicon-heart-empty text-info" style="font-size:64px;"></i>
			</div>
			<div class="media-body">
			<h4 class="media-heading text-info">
				<{$v.title}>
				<a href="<{'manual'}>/<{$v.id}>" class="pull-right">查看手册</a>
			</h4>
			<pre class="media-body-container">
				<{$v.content}>
				<button class="btn btn-link btn-default btn-block btn-sm media-body-expand-btn"><i class="glyphicon glyphicon-menu-down"></i></button>
			</pre>
			
			</div>
		</div>
		<{/foreach}>
	</div>
</div>
<{/block}>

<{block "body-scripts"}>
<script>
(function($){
$().ready(function(){
	$('.media-body-container').each(function(){
		if (this.scrollHeight > 250)
		{
			var $this = $(this);
			$('.media-body-expand-btn', this).show().on('click', function(){
				$(this).hide().closest('.media-body-container').height($this[0].scrollHeight);
			});
		}
			
	});
});
})(jQuery);
</script>
<{/block}>