$(document).ready(function(){

    $(".bd_sorting").on("click", function(e){
        $(this).toggleClass("on");
        if($(this).hasClass("on")){
            $(".board-sorting-area").removeClass("xe-hidden-xs");
            $(".board .bd_dimmed").show();
        } else{
            $(".board-sorting-area").addClass("xe-hidden-xs");
            $(".board .bd_dimmed").hide();
        }
        return false;
    });

    $(".bd_manage").on("click", function(e){
        $(this).toggleClass("on");
        if($(this).hasClass("on")){
            $(".bd_manage_detail").show();
        } else{
            $(".bd_manage_detail").hide();
        }
        return false;
    });

    $(".bd_select").on("click", function(e){
        $(this).parent().toggleClass("on");
        $(this).toggleClass("on");
        return false;
    });

    $(".bd_search").on("click", function(e){
        $(this).toggleClass("on");

        if($(this).hasClass("on")){
            $(".bd_search_area").show();
            $(".bd_search_input").focus();
        } else{
            $(".bd_search_area").hide();
        }

        $(".bd_btn_detail").on("click", function(e){
            $(this).toggleClass("on");
            if($(this).hasClass("on")){
                $(".bd_search_detail").show();
            } else{
                $(".bd_search_detail").hide();
            }
            return false;
        });
        return false;
    });

    $(".read_header .mb_autohr, .bd_like, .bd_favorite, .bd_btn_file, .bd_share, .bd_more_view, .bd_like_num, .btn_file, .like_num, .like, .share, .reply, .comment_more_view, .author, .mb_autohr, .comment_modify").on("click", function(e){

        $(this).toggleClass("on");
        if($(this).hasClass("bd_like_num")){
            $(".bd_like_more").toggle();
        }

        if($(this).hasClass("like_num")){
            $(this).parent().parent().find(".vote_list").toggle();
        }

        if($(this).hasClass("reply")){
            var el = $(this).parent().parent().find(".comment_action_area");
            var el2 = $(this).parent().parent().find(".comment_action_area.modify");
            el.toggle();
            el2.toggle();
        }

        if($(this).hasClass("comment_modify")){
            $(this).parent().parent().parent().find(".comment_action_area.modify, .xe_content, .comment_action").toggle();
        }

        if($(this).hasClass("temp_save_num")){
            $(".temp_save_list").toggle();
        }

        return false;
    });

})
