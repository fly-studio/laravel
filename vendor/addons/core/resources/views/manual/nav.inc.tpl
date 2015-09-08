<!-- Navigation -->
<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
	<div class="navbar-header">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
		<a class="navbar-brand" href="<{'manual'|url}>">手册合集</a>
	</div>
	<!-- /.navbar-header -->


	<div class="navbar-default sidebar" role="navigation">
		<div class="sidebar-nav navbar-collapse">
			<ul class="nav" id="side-menu">
				<li class="sidebar-search">
					<div class="input-group custom-search-form">
						<input type="text" class="form-control" placeholder="Search...">
						<span class="input-group-btn">
						<button class="btn btn-default" type="button">
							<i class="fa fa-search"></i>
						</button>
					</span>
					</div>
					<!-- /input-group -->
				</li>
				<li>
					<a href="<{'manual'|url}>/<{$_root.id}>"><i class="fa fa-dashboard fa-fw"></i> <{$_root.title}></a>
				</li>
				
			</ul>
		</div>
		<!-- /.sidebar-collapse -->
	</div>
	<!-- /.navbar-static-side -->
</nav>

<script type="text/javascript">
(function($){
	$().ready(function(){

		var tree = <{$_tree|json_encode nofilter}>;
		var recursive = function(items)
		{
			var html = '';
			for(var i = 0; i < items.length; ++i) {
				var v = items[i];
				html += '<li>';
				html += '<a href="'+$.baseuri+'manual/'+v['id']+'">' + v['title'] + (v['children'] ? '<span class="fa fa-sort-desc arrow"></span>' : '') +'</a>';
				if (v['children']) {
					html += '<ul class="nav nav-second-level">';
					html += recursive(v['children']);
					html += '</ul>';
				}
				html += '</li>';
			};
			return html;
		}
		if (tree[0])
		{
			var html = recursive(tree[0]['children']);
			$('#side-menu').append(html);
		}
		
		$(window).bind("load resize", function() {
			topOffset = 50;
			width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
			if (width < 768) {
				$('div.navbar-collapse').addClass('collapse');
				topOffset = 100; // 2-row-menu
			} else {
				$('div.navbar-collapse').removeClass('collapse');
			}

			height = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1;
			height = height - topOffset;
			if (height < 1) height = 1;
			if (height > topOffset) {
				$("#page-wrapper").css("min-height", (height) + "px");
			}
		});
	});
	
})(jQuery);
</script>