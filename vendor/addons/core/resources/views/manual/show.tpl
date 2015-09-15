<{extends file="extends/main.block.tpl"}>

<{block "head-styles-plus"}>
<link rel="stylesheet" href="<{'static/css/bootstrap-sidebar/sb-admin-2.css'|url}>" />
<link rel="stylesheet" href="<{'static/js/editor.md/css/editormd.preview.css'|url}>" />
<link rel="stylesheet" href="<{'static/css/font-awesome.min.css'|url}>" />
<style>
body {font-family: "Helvetica Neue", Helvetica, Microsoft Yahei, Hiragino Sans GB, WenQuanYi Micro Hei, sans-serif;}
.nav>li>a {position: relative;display: block;padding: 10px 15px;}
#affix-side .nav>li>a {display: block;padding: 4px 20px;font-size: 13px;font-weight: 500;color: #999;}
#affix-side .nav>.active>a, #affix-side .nav>.active:hover>a, #affix-side .nav>.active:focus>a {padding-left: 18px;font-weight: 700;color: #563d7c;background-color: transparent;border-left: 2px solid #563d7c;}
#affix-side .nav .nav>li>a {padding-top: 1px;padding-bottom: 1px;padding-left: 30px;font-size: 12px;font-weight: 400;}
#affix-side .nav .nav>.active>a, #affix-side .nav .nav>.active:hover>a, #affix-side .nav .nav>.active:focus>a {padding-left: 28px;font-weight: 500;}
#affix-side .nav li ul {display: none; }
#affix-side .nav li.active ul {display: block;}

#affix-side {overflow: auto}
</style>
<{/block}>

<{block "head-scripts-plus"}>
<script>var $ = jQuery;</script>
<script src="<{'static/js/editor.md/lib/marked.min.js'|url}>"></script>
<script src="<{'static/js/editor.md/lib/prettify.min.js'|url}>"></script>
<script src="<{'static/js/editor.md/lib/raphael.min.js'|url}>"></script>
<script src="<{'static/js/editor.md/lib/underscore.min.js'|url}>"></script>
<script src="<{'static/js/editor.md/lib/sequence-diagram.min.js'|url}>"></script>
<script src="<{'static/js/editor.md/lib/flowchart.min.js'|url}>"></script>
<script src="<{'static/js/editor.md/lib/jquery.flowchart.min.js'|url}>"></script>
<script src="<{'static/js/editor.md/editormd.min.js'|url}>"></script>
<{/block}>

<{block "body-container"}>
<div id="wrapper">
	<{include file="manual/nav.inc.tpl"}>
	<div id="page-wrapper">
		<div class="row">
			<div class="col-lg-12">
				<div class="page-header">
					<h1>
					<{$_data.title}>
					<small><a href="<{'manual'|url}>/<{$_data.id}>/edit">[编辑]</a></small>
					</h1>
					Updated at <{$_data.updated_at}>, version : <{$_data->histories()->count() / 10}>
				</div>
				
				<ol class="breadcrumb">
					<{foreach $_parents->reverse() as $item}>
					<li>
						<a href="<{'manual'|url}>/<{$item.id}>"><{$item->title}></a>
					</li>
					<{/foreach}>
					<li class="active">
						 <{$_data.title}>
					</li>
				</ol>
			</div>
		</div>
		<div class="row">
			<div class="col-md-9 col-xs-12" >
				<{if empty($_data.content)}>
				<h3 class="page-header">请查看分类内容</h3>
				<ul>
					<{foreach $_data->getChildren() as $item}>
					<li>
						<h4><a href="<{'manual'}>/<{$item.id}>"><{$item.title}></a></h4>
						<pre><code><{$item.content|truncate:250}></code></pre>
					</li>
					<{/foreach}>
				</ul>
				<{/if}>
				<div id="editormd-view"></div>

			</div>
			<div class="col-md-3 col-xs-hidden">
				<div data-spy="affix" data-offset-top="0" id="affix-side">
					<h4>Table of Contents</h4>
					<ul class="nav" id="navbar"></ul>
				</div>

			</div>
		</div>
	</div>
</div>
<textarea id="markdown" class="hidden"><{$_data.content}></textarea>
<{/block}>

<{block "body-scripts"}>
<script>
(function($){
// You can custom @link base url.
//editormd.urls.atLinkBase = $.baseuri + 'member/';
editormd.markdownToHTML("editormd-view", {
	markdown        : "\r\n" + $("#markdown").text(),
	htmlDecode      : "style,script,iframe",  // you can filter tags decode
	//atLink    : true,    // enable @link
	//emailLink : true,    // enable email address auto link
	toc             : false,
	tocm            : true,    // Using [TOCM]
	//tocContainer    : "#affix-side", // 自定义 ToC 容器层
	//gfm             : false,
	//tocDropdown     : true,
	// markdownSourceCode : true, // 是否保留 Markdown 源码，即是否删除保存源码的 Textarea 标签
	emoji           : true,
	taskList        : true,
	tex             : true,  // 默认不解析
	flowChart       : true,  // 默认不解析
	sequenceDiagram : true  // 默认不解析
});
//添加导航
$('h1[id]', '#editormd-view').each(function(i){
	var $this = $(this);
	$this.attr('id', this.id + i);
	var $obj = $('<li><a href="#'+$this.attr('id')+'">'+$this.text()+'</a><ul class="nav"></ul></li>').appendTo("#navbar");
	$obj = $('ul', $obj);
	$this.nextUntil("h1",'h2').each(function(m){
		var $_this = $(this);
		$_this.attr('id', this.id + m);
		$obj.append('<li><a href="#'+$_this.attr('id')+'">'+$_this.text()+'</a></li>');
	});
});
//滚动监听
$('body').scrollspy({ target: '#navbar' });
//侧边栏
//$('#side-menu').metisMenu();

})(jQuery);
</script>
<{/block}>