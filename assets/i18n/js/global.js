// jQuery(function($) {
//  $.i18n().load({
//  'en': 'http://localhost/upd_template/i18n/languages/en.json',
//  'fr': 'http://localhost/upd_template/i18n/languages/fr.json'
//  });
// });
jQuery(function($) {

  var translate_funct = function() {
   $('html').i18n();
   $('#welcome').text($.i18n('welcome'));
   // console.log($.i18n('{{abbr:www|world wide web}}'));
   // $('#welcome').text(
   // $.i18n('welcome', current_user.name)
   // );
   // $('#consulting_chat').text(
   // $.i18n('consulting_info', consultant.name, consultant.gender, consultant.message)
   // );
   // $('#unread_messages').text(
   // $.i18n('unread_messages', 101)
   // );
   }



 $.i18n().load({
   'en': './assets/i18n/languages/en.json',
   'fr': './assets/i18n/languages/fr.json'
 }).done(function() {
 $('.locale-switcher').on('click', 'a', function(e) {
 e.preventDefault();
 console.log($(this).data('locale'));
 $.i18n().locale = $(this).data('locale');
 translate_funct();
 });
 translate_funct();
 });
});
