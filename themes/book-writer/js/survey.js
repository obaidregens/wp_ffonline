const survey_bar_message = 'Help us improve.';
const survey_modal_title = 'Help us improve.';
const survey_question_rating = 'Rate your experience on a scale of 1 to 5.';
const survey_rating_label_left = 'Awful';
const survey_rating_label_right = 'Excellent';
const survey_question_suggestion = 'What, specifically, can we do to make Fanfiction Online better?';
const survey_label_suggestion = 'How can we improve?';
const survey_question_email = 'Leave your email if you want us to get in touch.';
const survey_label_email = 'Email';
const survey_empty_message = 'Pwease?';
const survey_thanks_message = 'Thanks :) We\'ll use your suggestion to make Fanfiction Online better.';

jQuery(document).ready(function(){
    jQuery('html').append('<style>@media screen and (max-width: 600px){.modal#survey_modal{width: 100%;}.modal#survey_modal .modal-content{padding: 14px;}}\
    .modal#survey_modal [type=radio]+span{padding-left:26px !important;}\
     #survey_top_bar{width: 100%; position: fixed; top: 0; z-index: 100; left: 0;  background: var(--theme-color); color: #b7bfc4; text-align: center;overflow:hidden;} \
     #survey_top_bar span.open_survey{cursor:pointer;line-height: 31px;margin-left: 45px;}#survey_top_bar a{color: black;float: right;z-index: 20;width: 45px;height:30px;box-shadow: none;}\
     .rating_labels{padding:0 7%;font-weight:bold;text-transform: uppercase;}\
     </style>\
     <div id="survey_modal" class="modal modal-large"><div class="modal-content"><h1 class="share_title">' + survey_modal_title + '</h1>\
    <a style="position:absolute;top:0;right:0;" class="modal-close btn-hover btn-floating btn-small waves-effect waves-light"><i class="fas fa-times"></i></a>\
    <form name="survey_form">\
    <div class="section"></div>\
    <p class="row">' + survey_question_rating + '</p>\
    </form></div></div>'
    );
    const container = document.createElement('div');
    container.setAttribute('class','flex-container');
    //Rows
    let rows = [
        ['1','2','3','4','5'],
        ['1','2','3','4','5'],
    ]
    for (let i = 0; i < rows.length; i++) {
        let row = rows[i];
        let row_elem = document.createElement('div');
        row_elem.setAttribute('class','flex-header');
        for (let j = 0; j < row.length; j++) {
            let col_val = row[j];
            let col = document.createElement('div');
            if (i == 0){
                col.innerText = col_val;
            }
            else{
                let radio_label = document.createElement('label');
                let radio_input = document.createElement('input');
                radio_input.setAttribute('type','radio');
                radio_input.setAttribute('name','rating');
                radio_input.setAttribute('value',col_val);
                let radio_span = document.createElement('span');
                radio_label.appendChild(radio_input);
                radio_label.appendChild(radio_span);
                col.appendChild(radio_label);
            }
            row_elem.appendChild(col);
        }
        container.appendChild(row_elem);
    }
    jQuery('[name="survey_form"]').append(container);
    jQuery('[name="survey_form"]').append('<div class="flex-container rating_labels"><div class="flex-header"><div style="text-align:left">' + survey_rating_label_left + '</div><div style="text-align:right">' + survey_rating_label_right + '</div></div></div>');
    jQuery('[name="survey_form"]').append('<div class="row"></div><div class="row">' + survey_question_suggestion + '</div><div class="row" style="padding: 0 4%;"><div class="input-field col s12"><textarea name="suggestions" class="materialize-textarea"></textarea><label>' + survey_label_suggestion + '</label></div></div>');
    jQuery('[name="survey_form"]').append('<div class="row"></div><div class="row">' + survey_question_email + '</div><div class="row" style="padding: 0 4%;"><div class="input-field col s12"><input class="validate" type="email" name="email"></input><label>' + survey_label_email + '</label></div></div>');
    jQuery('[name="survey_form"]').append('<div class="btn-wrapper alignright"><button class="waves-effect waves-light btn-small" type="submit">Submit</button></div>')
    jQuery('#survey_modal').modal();
    jQuery('form[name="survey_form"]').submit(function(){
        event.preventDefault();
        const rating = jQuery(this).find('[name="rating"]:checked').attr('value');
        const suggestion = jQuery(this).find('[name="suggestions"]').val();
        const email = jQuery(this).find('[name="email"]').val();
        jQuery('.progress').css('display','block');
        jQuery.ajax({
            url: '/wp-content/themes/book-writer/php/post_survey.php',
            type: 'post',
            dataType: 'JSON',
            data: {rating, suggestion},
            success: function(response){
                M.Toast.dismissAll();
                if (response.code <= 5){
                    M.toast({html: survey_thanks_message});
                    jQuery('#survey_modal').modal('close');
                }
                else if (response.code == 7) {
                    M.toast({html: survey_empty_message});
                }
                else if (response.code > 5){
                    M.toast({html: 'An error occured.'});
                }
                jQuery('.progress').css('display','none');
            },
        });
    });
    jQuery('html').append('<div id="survey_top_bar"><a class="close_survey_bar btn-small waves-effect waves-light"><i class="fas fa-times"></i></a><span class="open_survey">' + survey_bar_message + '</span></div>');
    jQuery('.open_survey').click(function(){
        jQuery('#survey_modal').modal('open');

    });
    function close_survey_bar(){
        jQuery('#survey_top_bar').css('position','absolute');
        jQuery('#survey_top_bar').css('height','31.4px');
        jQuery('#survey_top_bar').children('a').css('display','none');
        jQuery('#survey_top_bar').children('span').css('margin-left','0');
        setCookie('improve_bar','close');
    }
    jQuery('.close_survey_bar').click(close_survey_bar);
    if (cookies['improve_bar'] && cookies['improve_bar'] == 'close'){
        close_survey_bar();
    }
});

