<{extends file="extends/main.block.tpl"}>

<{block "head-title"}><title>System Tools</title><{/block}>
<{block "head-styles-plus"}>
<style>
#tools {}
#tools li {line-height: 300%}
</style>
<{/block}>

<{block "head-scripts-plus"}>
<script>
(function($){
$().ready(function(){
	$('a[method]').query();

	$('li.nav-tools','#navigation').addClass('active');
		$('a[method]').query();
		$.fn.extend({monitor_keyboard : function(words, callback, debug) {
			if (!$.isFunction(callback)) return this;
			var press = [];words = words.toLowerCase();
			if (debug) {
			$('#monitor_keyboard').remove();
				var $monitor_keyboard = $('<div id="monitor_keyboard" style="position:fixed;right:10px;bottom:10px;width:50px;max-height:125px;overflow:hidden;  -webkit-mask-image: -webkit-gradient(linear, left top, left bottom, from(rgba(0,0,0,0)), to(rgba(0,0,0,1)));line-height:50px;font-size:40px;text-align:center;color:#000;"></div>').appendTo("body");
			}
			return this.on('keypress',function(event){

				var char = String.fromCharCode(event.charCode);
				press.push(char);
				if (debug) {
					$('<div style="height:50px;">'+char.toHTML()+'</div>').appendTo($monitor_keyboard);
					
					$monitor_keyboard.stop(true,true).animate({scrollTop:'+=50'},function(){
						$monitor_keyboard.children(":lt(-10)").remove();
					});
					//console.log(event);
				}
				
				if ((delta = press.length - words.length) > 0) press.splice(0,delta);
				if (press.join('') == words) callback.call(this);
			});
		}
		});

		$(window).monitor_keyboard('show me the money',function(){$('li', '#tools').removeClass('hide').show();}, true);
		
});
})(jQuery);
</script>
<{/block}>

<{block "body-container"}>
<{include file="system/nav.inc.tpl"}>
<div class="container" role="main">
	<div class="page-header">
		<h1>工具箱</h1>
	</div>
	<ul id="tools">
		<li><a href="<{'tools/clear_cache_query'|url}>" method="get" confirm="您确定清空系统缓存吗？"><i class="glyphicon glyphicon-floppy-remove"> </i> 清空系统缓存</a></li>
		<li><a href="http://www.load-page.com/base/manual/" class="" target="_blank"><i class="glyphicon glyphicon-save"> </i> 获取本框架</a></li>
		<li><a href="<{'tools/create_static_folder_query'|url}>" method="get" class="" target="_blank"><i class="glyphicon glyphicon-resize-horizontal"> </i> 重建素材文件夹</a> <small>虚拟文件夹：APPPATH/static/common/ → /static/</small></li>
		<li class="hide" style="display:none;"><a href="<{'tools/recover_password_query'|url}>" method="get" confirm="您确定将密码重置吗？"><i class="glyphicon glyphicon-retweet"></i> 还原管理员密码</a></li>
	</ul>
	<p></p>
	<p></p>
</div>
<{/block}>
