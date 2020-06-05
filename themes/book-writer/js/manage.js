const data = JSON.parse(document.querySelector('json_data').innerHTML);
for (let i = 0; i < data.length; i++) {
    let element = data[i];
    let time = timestamp_to_local(element.timestamp);
    jQuery('tbody').append('<tr vfs="' + element.cookie_id + '"><td>' + element.user_id + '</td><td>' + time + '</td><td>' + (element.IP) + '</td></tr>');
}
jQuery('tr').click(function(){
    jQuery('#track_user').modal('open');
    spin('.user-info');
    api('manage',{
        data: {
            vfs:this.getAttribute('vfs'),
        },
		callback: function(response){
            const user_data = JSON.parse(response);
            const type_icon = {book: 'menu_book',chapter: 'edit',page: 'description', home: 'home', collection: 'collections_bookmark', author: 'person',survey: 'poll'};
            const type_color = {book: 'purple',chapter: 'yellow',page: 'green', home: 'blue', collection: 'brown darken-1', author: 'pink lighten-1', survey: 'orange darken-4'};
            let construct = "";
            for (let i = 0; i < user_data.length; i++) {
                let session = user_data[i];
                let title = "Session Start";
                let p_content = 'Session Duration: ' + timestamp_duration(
                    session[session.length-1][session[session.length-1].length-1].timestamp -
                    session[0][0].timestamp
                );
                let icon = "alarm_on";
                let color = 'green';
                construct += '<div class="timeline-event"><div class="card timeline-content"><div class="card-content"><a class="card-title">' + title + '</a><p>' + p_content + '</p></div></div><div class="timeline-badge ' + color + ' white-text"><i class="material-icons">' + icon + '</i></div></div>';    
                for (let j = 0; j < session.length; j++) {
                    let stat = session[j];
                    title = ucfirst(stat[0].stat) + ' | ' + ucfirst(stat[0].type);
                    p_content = "";
                    icon = type_icon[stat[0].type];
                    color = type_color[stat[0].type];
                    for (let p = 0; p < stat.length; p++) {
                        let hit = stat[p];
                        p_content += hit.link + ' - ' + timestamp_to_local(hit.timestamp) + '<br>';
                    }
                    construct += '<div class="timeline-event"><div class="card timeline-content"><div class="card-content"><a class="card-title">' + title + '</a><p>' + p_content + '</p></div></div><div class="timeline-badge ' + color + ' white-text"><i class="material-icons">' + icon + '</i></div></div>';
                }
                title = "Session End";
                p_content = "";
                icon = "alarm_off";
                color = 'red';
                construct += '<div class="timeline-event"><div class="card timeline-content"><div class="card-content"><a class="card-title">' + title + '</a><p>' + p_content + '</p></div></div><div class="timeline-badge ' + color + ' white-text"><i class="material-icons">' + icon + '</i></div></div>';    
            }
            jQuery('.user_info').children().remove();
            jQuery('.user_info').append(construct);
		}
	});
});
