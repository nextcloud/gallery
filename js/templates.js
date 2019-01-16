(function() {
  var template = Handlebars.template, templates = Gallery.Templates = Gallery.Templates || {};
templates['breadcrumb'] = template({"1":function(container,depth0,helpers,partials,data) {
    var stack1, helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=helpers.helperMissing, alias3="function", alias4=container.escapeExpression;

  return "	<div class=\"crumb "
    + alias4(((helper = (helper = helpers.cssClass || (depth0 != null ? depth0.cssClass : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"cssClass","hash":{},"data":data}) : helper)))
    + "\" data-dir=\""
    + alias4(((helper = (helper = helpers.dir || (depth0 != null ? depth0.dir : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"dir","hash":{},"data":data}) : helper)))
    + "\">\n"
    + ((stack1 = helpers["if"].call(alias1,(depth0 != null ? depth0.link : depth0),{"name":"if","hash":{},"fn":container.program(2, data, 0),"inverse":container.program(8, data, 0),"data":data})) != null ? stack1 : "")
    + "	</div>\n";
},"2":function(container,depth0,helpers,partials,data) {
    var stack1, helper, alias1=depth0 != null ? depth0 : (container.nullContext || {});

  return "			<a href=\""
    + container.escapeExpression(((helper = (helper = helpers.link || (depth0 != null ? depth0.link : depth0)) != null ? helper : helpers.helperMissing),(typeof helper === "function" ? helper.call(alias1,{"name":"link","hash":{},"data":data}) : helper)))
    + "\">\n"
    + ((stack1 = helpers["if"].call(alias1,(depth0 != null ? depth0.img : depth0),{"name":"if","hash":{},"fn":container.program(3, data, 0),"inverse":container.program(6, data, 0),"data":data})) != null ? stack1 : "")
    + "			</a>\n";
},"3":function(container,depth0,helpers,partials,data) {
    var stack1;

  return ((stack1 = helpers["with"].call(depth0 != null ? depth0 : (container.nullContext || {}),(depth0 != null ? depth0.img : depth0),{"name":"with","hash":{},"fn":container.program(4, data, 0),"inverse":container.noop,"data":data})) != null ? stack1 : "");
},"4":function(container,depth0,helpers,partials,data) {
    var helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=helpers.helperMissing, alias3="function", alias4=container.escapeExpression;

  return "						<img title=\""
    + alias4(((helper = (helper = helpers.title || (depth0 != null ? depth0.title : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"title","hash":{},"data":data}) : helper)))
    + "\" src=\""
    + alias4(((helper = (helper = helpers.imageSrc || (depth0 != null ? depth0.imageSrc : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"imageSrc","hash":{},"data":data}) : helper)))
    + "\">\n";
},"6":function(container,depth0,helpers,partials,data) {
    var helper;

  return "					"
    + container.escapeExpression(((helper = (helper = helpers.name || (depth0 != null ? depth0.name : depth0)) != null ? helper : helpers.helperMissing),(typeof helper === "function" ? helper.call(depth0 != null ? depth0 : (container.nullContext || {}),{"name":"name","hash":{},"data":data}) : helper)))
    + "\n";
},"8":function(container,depth0,helpers,partials,data) {
    var helper;

  return "			<span>"
    + container.escapeExpression(((helper = (helper = helpers.name || (depth0 != null ? depth0.name : depth0)) != null ? helper : helpers.helperMissing),(typeof helper === "function" ? helper.call(depth0 != null ? depth0 : (container.nullContext || {}),{"name":"name","hash":{},"data":data}) : helper)))
    + "</span>\n";
},"compiler":[7,">= 4.0.0"],"main":function(container,depth0,helpers,partials,data) {
    var stack1;

  return ((stack1 = helpers.each.call(depth0 != null ? depth0 : (container.nullContext || {}),(depth0 != null ? depth0.crumbs : depth0),{"name":"each","hash":{},"fn":container.program(1, data, 0),"inverse":container.noop,"data":data})) != null ? stack1 : "");
},"useData":true});
templates['galleryalbum'] = template({"compiler":[7,">= 4.0.0"],"main":function(container,depth0,helpers,partials,data) {
    var helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=helpers.helperMissing, alias3="function", alias4=container.escapeExpression;

  return "<a class=\"row-element\" style=\"width: "
    + alias4(((helper = (helper = helpers.targetWidth || (depth0 != null ? depth0.targetWidth : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"targetWidth","hash":{},"data":data}) : helper)))
    + "px; height: "
    + alias4(((helper = (helper = helpers.targetHeight || (depth0 != null ? depth0.targetHeight : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"targetHeight","hash":{},"data":data}) : helper)))
    + "px;\" data-width=\""
    + alias4(((helper = (helper = helpers.targetWidth || (depth0 != null ? depth0.targetWidth : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"targetWidth","hash":{},"data":data}) : helper)))
    + "\" data-height=\""
    + alias4(((helper = (helper = helpers.targetHeight || (depth0 != null ? depth0.targetHeight : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"targetHeight","hash":{},"data":data}) : helper)))
    + "\" href=\""
    + alias4(((helper = (helper = helpers.targetPath || (depth0 != null ? depth0.targetPath : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"targetPath","hash":{},"data":data}) : helper)))
    + "\" data-dir=\""
    + alias4(((helper = (helper = helpers.dir || (depth0 != null ? depth0.dir : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"dir","hash":{},"data":data}) : helper)))
    + "\" data-path=\""
    + alias4(((helper = (helper = helpers.path || (depth0 != null ? depth0.path : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"path","hash":{},"data":data}) : helper)))
    + "\" data-permissions=\""
    + alias4(((helper = (helper = helpers.permissions || (depth0 != null ? depth0.permissions : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"permissions","hash":{},"data":data}) : helper)))
    + "\" data-freespace=\""
    + alias4(((helper = (helper = helpers.freeSpace || (depth0 != null ? depth0.freeSpace : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"freeSpace","hash":{},"data":data}) : helper)))
    + "\">\n	<div class=\"album-loader loading\"></div>\n	<span class=\"album-label\">\n		<span class=\"title\">"
    + alias4(((helper = (helper = helpers.label || (depth0 != null ? depth0.label : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"label","hash":{},"data":data}) : helper)))
    + "</span>\n	</span>\n	<div class=\"album container\" style=\"width: "
    + alias4(((helper = (helper = helpers.targetWidth || (depth0 != null ? depth0.targetWidth : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"targetWidth","hash":{},"data":data}) : helper)))
    + "px; height: "
    + alias4(((helper = (helper = helpers.targetHeight || (depth0 != null ? depth0.targetHeight : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"targetHeight","hash":{},"data":data}) : helper)))
    + "px;\" >\n	</div>\n</a>\n";
},"useData":true});
templates['galleryimage'] = template({"compiler":[7,">= 4.0.0"],"main":function(container,depth0,helpers,partials,data) {
    var helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=helpers.helperMissing, alias3="function", alias4=container.escapeExpression;

  return "<a class=\"row-element\" style=\"width: "
    + alias4(((helper = (helper = helpers.targetWidth || (depth0 != null ? depth0.targetWidth : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"targetWidth","hash":{},"data":data}) : helper)))
    + "px; height: "
    + alias4(((helper = (helper = helpers.targetHeight || (depth0 != null ? depth0.targetHeight : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"targetHeight","hash":{},"data":data}) : helper)))
    + "px;\" href=\"\" data-path=\""
    + alias4(((helper = (helper = helpers.path || (depth0 != null ? depth0.path : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"path","hash":{},"data":data}) : helper)))
    + "\">\n	<div class=\"image-loader loading\"></div>\n	<span class=\"image-label\">\n		<span class=\"title\">"
    + alias4(((helper = (helper = helpers.label || (depth0 != null ? depth0.label : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"label","hash":{},"data":data}) : helper)))
    + "</span>\n	</span>\n	<div class=\"image container\"></div>\n</a>\n";
},"useData":true});
templates['newfilemenu'] = template({"1":function(container,depth0,helpers,partials,data) {
    var helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=helpers.helperMissing, alias3="function", alias4=container.escapeExpression;

  return "		<li>\n			<a href=\"#\" class=\"menuitem\" data-action=\""
    + alias4(((helper = (helper = helpers.id || (depth0 != null ? depth0.id : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"id","hash":{},"data":data}) : helper)))
    + "\"><span class=\"icon "
    + alias4(((helper = (helper = helpers.iconClass || (depth0 != null ? depth0.iconClass : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"iconClass","hash":{},"data":data}) : helper)))
    + " svg\"></span><span class=\"displayname\">"
    + alias4(((helper = (helper = helpers.displayName || (depth0 != null ? depth0.displayName : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"displayName","hash":{},"data":data}) : helper)))
    + "</span></a>\n		</li>\n";
},"compiler":[7,">= 4.0.0"],"main":function(container,depth0,helpers,partials,data) {
    var stack1, helper, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=helpers.helperMissing, alias3="function", alias4=container.escapeExpression;

  return "<ul>\n	<li>\n		<label for=\"file_upload_start\" class=\"menuitem\" data-action=\"upload\" title=\""
    + alias4(((helper = (helper = helpers.uploadMaxHumanFilesize || (depth0 != null ? depth0.uploadMaxHumanFilesize : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"uploadMaxHumanFilesize","hash":{},"data":data}) : helper)))
    + "\"><span class=\"svg icon icon-upload\"></span><span class=\"displayname\">"
    + alias4(((helper = (helper = helpers.uploadLabel || (depth0 != null ? depth0.uploadLabel : depth0)) != null ? helper : alias2),(typeof helper === alias3 ? helper.call(alias1,{"name":"uploadLabel","hash":{},"data":data}) : helper)))
    + "</span></label>\n	</li>\n"
    + ((stack1 = helpers.each.call(alias1,(depth0 != null ? depth0.items : depth0),{"name":"each","hash":{},"fn":container.program(1, data, 0),"inverse":container.noop,"data":data})) != null ? stack1 : "")
    + "</ul>\n";
},"useData":true});
})();
