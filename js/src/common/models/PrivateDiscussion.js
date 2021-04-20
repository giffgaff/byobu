import Discussion from 'flarum/models/Discussion';
import Model from 'flarum/Model';

export default class PrivateDiscussion extends Discussion {
    recipientUsers = Model.hasMany('recipientUsers');
    oldRecipientUsers = Model.hasMany('oldRecipientUsers');
    canEditRecipients = Model.attribute('canEditRecipients');
    canEditUserRecipients = Model.attribute('canEditUserRecipients');
}
