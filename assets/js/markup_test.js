$(document).ready(function(){

    $(".bd_sorting").on("click", function(e){
        $(this).toggleClass("on");
        if($(this).hasClass("on")){
            $(".bd_sorting_area").removeClass("mb_hidden");
            $(".board .bd_dimmed").show();
        } else{
            $(".bd_sorting_area").addClass("mb_hidden");
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

    $(".read_header .mb_autohr, .bd_like, .bd_favorite, .bd_btn_file, .bd_share, .bd_more_view, .bd_like_num, .btn_file, .like_num, .like, .share, .reply, .comment_more_view, .author, .mb_autohr").on("click", function(e){

        $(this).toggleClass("on");
        if($(this).hasClass("bd_like_num")){
            $(".bd_like_more").toggle();
        }

        if($(this).hasClass("like_num")){
            $(this).parent().parent().find(".vote_list").toggle();
        }

        if($(this).hasClass("reply")){
            $(this).parent().parent().find(".comment_action_area").toggle();
        }

        return false;
    });


})
