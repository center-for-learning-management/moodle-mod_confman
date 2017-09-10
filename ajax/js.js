// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Links and settings
 * @package    mod_confman
 * @copyright  2017 Digital Education Society (http://www.dibig.at)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

var mod_confman = {
     id: 0,
     token: '',
     spinner: '<img src="img/spinner.gif" />',
     fileAppend: function(id,token){
          console.log('mod_confman.fileAppend('+id+','+token+')');
          mod_confman.id = id;
          mod_confman.token = token;
          var filename = $('#item-file')[0].files[0].name.replace(/ /g,'_');
          var file = $('#item-file')[0].files[0];
          var reader  = new FileReader();
          reader.addEventListener("load", function () {
               mod_confman.call({ act: 'file_append', filename: filename, file: reader.result });
          }, false);

          if(file) {
               reader.readAsDataURL(file);
          }
          
          var li = $('li[data-filename="'+filename+'"]');
          if(li.length==0) {
               li = $('<li>').attr('data-filename',filename);
               var a1 = $('<a>').html(filename).attr('target','_blank').attr('data-ajax','false');
               var a2 = $('<a>').html('delete').attr('href','#');
               li.append(a1).append(a2);
               $('#item-files').append(li);
          }
          $('li[data-filename="'+filename+'"] a:first-child').html(filename);
          li.addClass('alert-loading');
          $('#item-files').listview('refresh');
     },
     fileDelete: function(id,token,filename){
          mod_confman.id = id;
          mod_confman.token = token;
          var li = $('li[data-filename="'+filename+'"]');
          li.addClass('alert-loading');

          mod_confman.call({ act: 'file_delete', filename: filename });
     },
     call: function(data){
          console.log('mod_confman.call('+data+')');
          console.log(data);
          
		$.ajax({
			url: 'ajax/php.php?id='+mod_confman.id+'&token='+mod_confman.token,
			method: 'POST',
			data: data,
		}).done(function(res){
		     console.log('Result is:');
		     console.log(res);
			try { res = JSON.parse(res); } catch(e){}
			console.log('Parsed:');
			console.log(res);
			mod_confman.result(data,res);
          }).fail(function(jqXHR,textStatus){
			console.error('ERROR');
			console.log(textStatus);
		}).always(function(){
    		   if(data.act=='file_append') $('#item-file').val('');
		});
	 },
      result: function(data,result){
          console.log('mod_confman.result('+data+','+result+')');
          console.log(data);
          console.log(result);
          
          if(data.act=='file_delete'){
               if(result.status=='ok'){
                    $('li[data-filename="'+data.filename+'"]').remove();
                    $('#item-files').listview('refresh');
               } else {
                    $('li[data-filename="'+data.filename+'"]').removeClass('alert-loading').addClass('alert-error');
               }
          }
          if(data.act=='file_append'){
               if(result.status=='ok'){
                    $('li[data-filename="'+data.filename+'"]').removeClass('alert-loading').removeClass('alert-error');
                    $('li[data-filename="'+data.filename+'"] a:first-child').attr('href',result.url).html(data.filename);
                    $('li[data-filename="'+data.filename+'"] a:last-child').attr('href','#').attr('onclick','mod_confman.fileDelete('+mod_confman.id+',\''+mod_confman.token+'\',\''+data.filename+'\');').html('delete');
                    try { $('#item-files').listview('refresh'); } catch(e){ console.error(e);}
               }
          }
          
      },

}
