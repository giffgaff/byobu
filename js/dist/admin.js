module.exports=function(t){var e={};function i(n){if(e[n])return e[n].exports;var a=e[n]={i:n,l:!1,exports:{}};return t[n].call(a.exports,a,a.exports,i),a.l=!0,a.exports}return i.m=t,i.c=e,i.d=function(t,e,n){i.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:n})},i.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},i.t=function(t,e){if(1&e&&(t=i(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(i.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var a in t)i.d(n,a,function(e){return t[e]}.bind(null,a));return n},i.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return i.d(e,"a",e),e},i.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},i.p="",i(i.s=48)}({0:function(t,e){t.exports=flarum.core.compat.extend},11:function(t,e){},12:function(t,e){t.exports=flarum.core.compat["components/Badge"]},20:function(t,e){t.exports=flarum.core.compat["components/PermissionGrid"]},24:function(t,e){t.exports=flarum.extensions["fof-components"]},26:function(t,e,i){"use strict";var n=i(46),a=i.n(n),r=i(0),s=i(20),o=i.n(s),u=function(){Object(r.extend)(o.a.prototype,"startItems",(function(t){t.add("startPrivateUsers",{icon:"far fa-map",label:app.translator.trans("fof-byobu.admin.permission.create_private_discussions_with_users"),permission:"discussion.startPrivateDiscussionWithUsers"},95),t.add("startPrivateGroups",{icon:"far fa-map",label:app.translator.trans("fof-byobu.admin.permission.create_private_discussions_with_groups"),permission:"discussion.startPrivateDiscussionWithGroups"},95),t.add("startPrivateBlockers",{icon:"far fa-map",label:app.translator.trans("fof-byobu.admin.permission.create_private_discussions_with_blocking_users"),permission:"startPrivateDiscussionWithBlockers"},95)})),Object(r.extend)(o.a.prototype,"replyItems",(function(t){t.add("makePrivatePublic",{icon:"far fa-map",label:app.translator.trans("fof-byobu.admin.permission.make_private_into_public"),permission:"discussion.makePublic"},95)})),Object(r.extend)(o.a.prototype,"moderateItems",(function(t){t.add("editUserRecipients",{icon:"far fa-map",label:app.translator.trans("fof-byobu.admin.permission.edit_user_recipients"),permission:"discussion.editUserRecipients"},95),t.add("editGroupRecipients",{icon:"far fa-map",label:app.translator.trans("fof-byobu.admin.permission.edit_group_recipients"),permission:"discussion.editGroupRecipients"},95),t.add("actor-can-view-private-discussions-when-flagged",{icon:"fas fa-flag",label:app.translator.trans("fof-byobu.admin.permission.view_private_discussions-when-flagged"),permission:"user.viewPrivateDiscussionsWhenFlagged"},95)}))},p=i(24),d=(i(12),p.settings.SettingsModal),c=p.settings.items,f=c.BooleanItem,l=(c.StringItem,c.SelectItem);function b(){var t={},e=app.store.all("tags").sort((function(t,e){return t.data.attributes.position-e.data.attributes.position})).reduce((function(e,i){return null===i.position()||(i.isChild()?void 0===t[i.data.relationships.parent.data.id]?t[i.data.relationships.parent.data.id]=[i.data.id]:t[i.data.relationships.parent.data.id].push(i.data.id):e[i.slug()]=i.name()),e}),{}),i={"":"--- No restriction ---"};for(var n in e){i[n]=e[n];var a=app.store.getBy("tags","slug",n);a.isPrimary()&&void 0!==t[a.data.id]&&t[a.data.id].forEach((function(t){var e=app.store.getBy("tags","id",t);i[e.slug()]=" └─ "+e.name()}))}return i}var g=function(){app.extensionSettings["fof-byobu"]=function(){return app.modal.show(new d({title:"FoF Byōbu",size:"medium",items:[m(f,{key:"fof-byobu.index_link"},app.translator.trans("fof-byobu.admin.settings.byobu_index")),m("p",null,app.translator.trans("fof-byobu.admin.settings.byobu_index_help")),m("div",{className:"Form-group"},m("label",null,app.translator.trans("fof-byobu.admin.settings.use_tag_slug")),l.component({options:b(),key:"fof-byobu.use_tag_slug",required:!1})),m("p",null,app.translator.trans("fof-byobu.admin.settings.use_tag_slug_help"))]}))}};app.initializers.add("fof-byobu",(function(t){t.store.models.recipients=a.a,u(),g()}))},46:function(t,e){t.exports=flarum.core.compat["core/models/User"]},48:function(t,e,i){"use strict";i.r(e);var n=i(11);for(var a in n)"default"!==a&&function(t){i.d(e,t,(function(){return n[t]}))}(a);i(26)}});
//# sourceMappingURL=admin.js.map