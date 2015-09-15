<{extends file="extends/main.block.tpl"}>

<{block "head-styles-plus"}>
<link rel="stylesheet" href="<{'static/css/font-awesome.min.css'|url}>" />
<style>
	.media-body-container{position: relative;}
	.media-body-container pre {height:250px; overflow: hidden; }
	.media-body-expand-btn {display: none; height:20px; width:100%; text-align: right; line-height: 15px; position: absolute; left: 0; right: 0; bottom: 5px;}
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
				<a href="<{'manual'|url}>/<{$v.id}>" class="pull-right">查看手册</a>
			</h4>
			<div class="media-body-container">
				<pre><code><{$v.content}></code></pre>
				<button class="btn btn-link btn-default btn-block btn-sm media-body-expand-btn"><i class="glyphicon glyphicon-menu-down"></i></button>

			</div>
			
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
	$('pre').each(function(){
		if (this.scrollHeight > 250)
		{
			var $this = $(this);
			$this.siblings('.media-body-expand-btn').show().on('click', function(){
				$(this).remove();
				$this.height($this[0].scrollHeight);
			});
		}
			
	});
});
})(jQuery);
</script>
<{/block}>