module.exports=function(t){var e={};function n(o){if(e[o])return e[o].exports;var i=e[o]={i:o,l:!1,exports:{}};return t[o].call(i.exports,i,i.exports,n),i.l=!0,i.exports}return n.m=t,n.c=e,n.d=function(t,e,o){n.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:o})},n.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},n.t=function(t,e){if(1&e&&(t=n(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var o=Object.create(null);if(n.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var i in t)n.d(o,i,function(e){return t[e]}.bind(null,i));return o},n.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return n.d(e,"a",e),e},n.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},n.p="",n(n.s=44)}({0:function(t,e,n){"use strict";function o(t,e){return(o=Object.setPrototypeOf||function(t,e){return t.__proto__=e,t})(t,e)}function i(t,e){t.prototype=Object.create(e.prototype),t.prototype.constructor=t,o(t,e)}n.d(e,"a",(function(){return i}))},11:function(t,e){t.exports=flarum.core.compat["components/Badge"]},39:function(t,e){t.exports=flarum.core.compat["core/models/User"]},40:function(t,e){t.exports=flarum.core.compat["components/ExtensionPage"]},41:function(t,e){t.exports=flarum.core.compat["helpers/icon"]},42:function(t,e){t.exports=flarum.extensions["fof-components"]},44:function(t,e,n){"use strict";n.r(e);var o=n(39),i=n.n(o),r=n(0),s=n(40),a=n.n(s),u=n(11),c=n.n(u),f=n(41),l=n.n(f),p=n(42).settings.items,b=(p.BooleanItem,p.SelectItem,p.StringItem),d=(p.NumberItem,function(t){function e(){return t.apply(this,arguments)||this}Object(r.a)(e,t);var n=e.prototype;return n.oninit=function(e){t.prototype.oninit.call(this,e),this.setting=this.setting.bind(this),this.badgeDefault="fas fa-map",this.postActionDefault="far fa-map"},n.content=function(){return[m("div",{className:"container"},m("div",{className:"ByobuSettingsPage"},m("div",{className:"Form-group"},m("label",null,app.translator.trans("fof-byobu.admin.settings.badge-icon")),m(b,{name:"fof-byobu.icon-badge",placeholder:this.badgeDefault,setting:this.setting},m(c.a,{icon:this.setting("fof-byobu.icon-badge").toJSON()||this.badgeDefault}))),m("div",{className:"Form-group"},m("label",null,app.translator.trans("fof-byobu.admin.settings.post-event-icon")),m(b,{name:"fof-byobu.icon-postAction",placeholder:this.postActionDefault,setting:this.setting},m("h2",null,l()(this.setting("fof-byobu.icon-postAction").toJSON()||this.postActionDefault)))),m("p",null,app.translator.trans("flarum-tags.admin.edit_tag.icon_text",{a:m("a",{href:"https://fontawesome.com/icons?m=free",tabindex:"-1"})})),m("div",{className:"Form-group"},this.submitButton())))]},e}(a.a));app.initializers.add("fof-byobu",(function(t){t.store.models.recipients=i.a,t.extensionData.for("fof-byobu").registerPage(d),function(t){t.extensionData.for("fof-byobu").registerPermission({icon:"far fa-map",label:t.translator.trans("fof-byobu.admin.permission.create_private_discussions_with_users"),permission:"discussion.startPrivateDiscussionWithUsers"},"start",95).registerPermission({icon:"far fa-map",label:t.translator.trans("fof-byobu.admin.permission.create_private_discussions_with_blocking_users"),permission:"startPrivateDiscussionWithBlockers"},"start",95).registerPermission({icon:"far fa-map",label:t.translator.trans("fof-byobu.admin.permission.edit_user_recipients"),permission:"discussion.editUserRecipients"},"moderate",95).registerPermission({icon:"fas fa-flag",label:t.translator.trans("fof-byobu.admin.permission.view_private_discussions-when-flagged"),permission:"user.viewPrivateDiscussionsWhenFlagged"},"moderate",95)}(t)}))}});
//# sourceMappingURL=admin.js.map