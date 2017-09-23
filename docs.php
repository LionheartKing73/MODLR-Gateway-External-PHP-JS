<?php

include_once("lib/lib.php");
include_once("lib/header.php");
?>
    	<!--external css-->
    	<link rel="stylesheet" type="text/css" href="/js/fuelux/css/tree-style.css" />
    	
		<title>MODLR » Documentation</title>
		<style>
		.tutorial_img {
			border:1px solid #999;
			margin:5px;
		}
		td.tdText {
			vertical-align:top;
			padding-right:5px;
		}
                
    #loader {
        display: none;
        position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 9999;
    background: rgba(255, 255, 255, 0.79);
    display: flex;
    align-items: center;
    justify-content: center;
    }
    
    .categories .panel:hover,
    .categories .panel.active {
        box-shadow: inset 0px -3px 0px #118dc6;
    }
		</style>
		
<?
include_once("lib/body_start.php");
?>
<div class="row">
        <?php if(session("client_id") == 1):?>
<nav class="navbar navbar-inverse" role="navigation" style="border-radius: 0px;top:-15px;position:relative;">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/documentation/">Documentation</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse navbar-ex1-collapse">

            <ul class="nav navbar-nav navbar-right">
                <li><a id="editEntryDocument" data-slug="<?php echo $document;?>">Edit</a></li>
                <li class="dropdown">
                        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">New <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a data-toggle="modal" data-target="#addNewEntry">Add New Entry</a></li>
                        </ul>
                </li>
            </ul>
    </div><!-- /.navbar-collapse -->
</nav>
    <?php endif; ?>
			<div class="col-lg-12" style="margin-bottom:15px;">
                <button type="button" class="btn btn-success btn-lg btn-block">DOCUMENTATION</button>
            </div>
		
<?php

function getArticleCountByGroup($document_category) { 
	$sql = "SELECT COUNT(*) as pages FROM documents WHERE document_category = '%s' GROUP BY document_category;";
	$db = new db_helper();
	$db->CommandText($sql);
	$db->Parameters($document_category);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		while( $r = $db->Rows() ) {
			return $r['pages'];
		}
	}
	return 0;
}


if( $iPad ) {
	echo '<div class="col-md-4">';
} else {
	echo '<div class="col-md-3">';
}
?>
				<section class="panel">
					<div class="panel-body" onclick="loadDocGroup('User_Guide');" style="cursor: pointer;">
						<div class="gauge-canvas" style="min-height: 86px;">
							<h4 class="widget-h" style="min-height: 34px;margin-bottom:0px;"><a href='#' target='_self' onclick="loadDocGroup('User_Guide');">User Guides</a></h4>
							
							<div class="weather-category twt-category" style="margin-top: 0px;margin-bottom: 0px;padding-bottom: 0px;padding-top: 0px;">
								<ul>
									<li class="active">
										<h5><? echo getArticleCountByGroup("User Guide");?></h5>
										Pages
									</li>
								</ul>
							</div>
						</div>
					</div>
				</section>     
        	</div>
<?php
if( $iPad ) {
	echo '<div class="col-md-4">';
} else {
	echo '<div class="col-md-3">';
}
?>
				<section class="panel">
					<div class="panel-body"  onclick="loadDocGroup('Modelling_Tutorials');" style="cursor: pointer;">
						<div class="gauge-canvas" style="min-height: 86px;">
							<h4 class="widget-h" style="min-height: 34px;margin-bottom:0px;"><a href='#' target='_self' onclick="loadDocGroup('Modelling_Tutorials');">Modelling Tutorials</a></h4>
							
							<div class="weather-category twt-category" style="margin-top: 0px;margin-bottom: 0px;padding-bottom: 0px;padding-top: 0px;">
								<ul>
									<li class="active">
										<h5><? echo getArticleCountByGroup("Modelling Tutorials");?></h5>
										Pages
									</li>
								</ul>
							</div>
						</div>
					</div>
				</section>     
        	</div>
		
<?php
if( $iPad ) {
	echo '<div class="col-md-4">';
} else {
	echo '<div class="col-md-3">';
}
?>
				<section class="panel">
					<div class="panel-body" onclick="loadDocGroup('Workview_Function_Reference');" style="cursor: pointer;">
						<div class="gauge-canvas" style="min-height: 86px;">
							<h4 class="widget-h" style="min-height: 34px;margin-bottom:0px;"><a href='#' target='_self' onclick="loadDocGroup('Workview_Function_Reference');">Workview Function Reference</a></h4>
							
							<div class="weather-category twt-category" style="margin-top: 0px;margin-bottom: 0px;padding-bottom: 0px;padding-top: 0px;">
								<ul>
									<li class="active">
										<h5><? echo getArticleCountByGroup("Workview Function Reference");?></h5>
										Pages
									</li>
								</ul>
							</div>
						</div>
					</div>
				</section>     
        	</div>
		
<?php
if( $iPad ) {
	echo '<div class="col-md-4">';
} else {
	echo '<div class="col-md-3">';
}
?>
				<section class="panel">
					<div class="panel-body" onclick="loadDocGroup('Process_Scripting_Reference');" style="cursor: pointer;">
						<div class="gauge-canvas" style="min-height: 86px;">
							<h4 class="widget-h" style="min-height: 34px;margin-bottom:0px;"><a href='#' target='_self' onclick="loadDocGroup('Process_Scripting_Reference');">Process Function Reference</a></h4>
							
							<div class="weather-category twt-category" style="margin-top: 0px;margin-bottom: 0px;padding-bottom: 0px;padding-top: 0px;">
								<ul>
									<li class="active">
										<h5><? echo getArticleCountByGroup("Process Scripting Reference");?></h5>
										Pages
									</li>
								</ul>
							</div>
						</div>
					</div>
				</section>     
        	</div>
			
			
			
			
        </div>

        <div class="row">

			<div class="col-md-4">
				<div class="panel">
					<div class="panel-heading">
						<div class="clearfix">
                    <div class="pull-left"><h4 style="padding: 0;margin: 0;position: relative;top: 8px;">Navigation</h4></div>
                    <div class="pull-right"><input type="text" id="filterList" class="form-control" onkeyup="filterList();" placeholder="Search for..."></div>
                </div>
					 
					</div>
					<div class="panel-body" style='padding:0px; height:450px; overflow-y:scroll;'>
<?php
	$sql = "SELECT DISTINCT document_category FROM documents;";
	$dbT = new db_helper();
	$dbT->CommandText($sql);
	$dbT->Execute();
	if ($dbT->Rows_Count() > 0) {
		while( $rT = $dbT->Rows() ) {
			$category = $rT['document_category'];
			outputCategory($category);
		}
	}
	

function outputCategory($document_category) {
	$category = str_replace(" ","_",$document_category);
	echo '<ul id="nav_'.$category.'" class="nav nav-pills nav-stacked mail-nav" style="margin-left:0px;margin-top: 0px;margin-right: 0px;">';
	
	$db = new db_helper();
	$db->CommandText("SELECT documents.document_id, documents.document_name, documents.document_parent, COUNT(subdocs.document_id) as subdocuments FROM modlr.documents LEFT JOIN modlr.documents AS subdocs ON subdocs.document_parent=documents.document_id WHERE documents.document_parent = 0 AND documents.document_category='%s' GROUP BY documents.document_id ORDER BY documents.document_order ASC;");
	$db->Parameters($document_category);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		while( $r = $db->Rows() ) {
			
			$document_id = $r['document_id'];
			$document_name = $r['document_name'];
			$subdocuments = $r['subdocuments'];
			$document_name = str_replace("'","\\'",$document_name);
			
			echo '<li class="list_title_item"><a href="#" onclick="loadPage('.$document_id.',\''.$document_name.'\');"> <i class="fa fa-file-text-o"></i> '.$document_name.' </a></li>';
			
			docsOutputChildren($document_id, 1);
			
			//<span class="label label-info pull-right inbox-notification">123</span>
		}
	}

	echo '</ul>';
}

function docsOutputChildren($parent_id , $indent) {
	$db = new db_helper();
	$db->CommandText("SELECT documents.document_id, documents.document_name, documents.document_parent, COUNT(subdocs.document_id) as subdocuments FROM modlr.documents LEFT JOIN modlr.documents AS subdocs ON subdocs.document_parent=documents.document_id WHERE documents.document_parent = ".$parent_id." GROUP BY documents.document_id ORDER BY documents.document_order ASC;");
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		while( $r = $db->Rows() ) {
		
			$document_id = $r['document_id'];
			$document_name = $r['document_name'];
			$subdocuments = $r['subdocuments'];
			$document_name = str_replace("'","\\'",$document_name);
		
			echo '<li style="padding-left:'.($indent * 20).'px;"><a href="#" onclick="loadPage('.$document_id.',\''.$document_name.'\');"> <i class="fa fa-file-text-o"></i> '.$document_name.' </a></li>';
		
			docsOutputChildren($document_id, $indent + 1 );
	
			//<span class="label label-info pull-right inbox-notification">123</span>
		}
	}
}
?>
                       
						
						
					</div>
				</div>
			</div>


			<div class="col-md-8">
				<div class="panel">
					<div class="panel-heading"  id='documentationHeading'>
						Documentation
					</div>
					<div class="panel-body" id='documentationPanel' style="height:450px; overflow-y:scroll;">
						
						
						
					</div>
				</div>
			</div>


        </div>
        <div class="modal fade" id="addNewCategory" tabindex="-1" role="dialog" aria-labelledby="addNewCategoryLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="addNewCategoryLabel">Add New Category</h4>
      </div>
      <div class="modal-body">
          <form class="form" action="" method="POST">
              <div class="form-group">
                  <label>Category Name</label>
                  <input type="text" class="form-control" name="category_name" placeholder="Category Name" />
              </div>
              
              <div class="form-group">
                  <input type="submit" name="addCategory" class="btn btn-success" value="Add Category" />
              </div>
          </form>
      </div>
    </div>
  </div>
</div>
              
<div class="modal fade" id="addNewEntry" tabindex="-1" role="dialog" aria-labelledby="addNewEntryLabel" data-backdrop="static">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="addNewEntryLabel">Add New Entry</h4>
      </div>
      <div class="modal-body">
          <form class="form" action="" id="addNewEntryForm" method="POST">
              <div class="form-group">
                  <label>Entry Name</label>
                  <input type="text" class="form-control" id="entry_name" placeholder="Entry Name" />
              </div>
              
              <div class="form-group">
                  <label>Entry Parent</label>
                  <select class="form-control" id="entry_parent">
                      <option value disabled>-- Select Category --</option>
                      <?php echo getCategorysOptions();?>
                  </select>
              </div>
              
              <div class="form-group">
                  <label>Entry Content</label>
                  <textarea class="form-control" id="editorNew" name="entry_content" rows="10"></textarea>
              </div>
              
              <div class="form-group">
                  <input type="submit" name="addEntry" class="btn btn-success" value="Add Category" />
              </div>
          </form>
      </div>
    </div>
  </div>
</div>
<div id="loader" style="display: none;">
<svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
     width="40px" height="40px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve">
  <path fill="#053b69" d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,14.615,6.543,14.615,14.615H43.935z">
    <animateTransform attributeType="xml"
      attributeName="transform"
      type="rotate"
      from="0 25 25"
      to="360 25 25"
      dur="0.6s"
      repeatCount="indefinite"/>
    </path>
  </svg>
</div>

<?php
include_once("lib/body_end.php");
?>
        
<!--tree-->
<script type="text/javascript" src="/js/ckeditor/ckeditor.js"></script>
<script src="/js/fuelux/js/tree.min.js"></script>
<!--script for this page-->
<script type='text/javascript'>
var current_page_id = 0;
function loadDocGroup(group) {
	$(".nav-stacked").css("display","none");
	$("#nav_" + group).css("display","block");
	$($("#nav_" + group + " > li.list_title_item > a")[0]).trigger( "click" );
}


function loadPage(pageId, pageName) {
    current_page_id = pageId;
	$("#documentationHeading").html(pageName);
	$.get( "/lib/docs/?id=" + pageId, function( data ) {
		$("#documentationPanel").html(data);
		
		$("img.galleryImage").click(function(event){
			event.preventDefault();
			PreviewImage($(this).attr('src'));
		  });    
	});
		
}

function filterList() {
    var value = $('#filterList').val();
    var list = $('#nav_User_Guide li a');
    for(var i = 0; i < list.length; i++) {
        var item = list[i];
        if(value.length === 0) {
            $(item).parent().show();
        } else {
           if($(item).text().toLowerCase().indexOf(value.toLowerCase()) > -1) {
               $(item).parent().show();
           } else {
               $(item).parent().hide();
           }
        }
    }
}

PreviewImage = function(uri) {
	
	$('<div></div>').appendTo('body')
    .html('<div><img width="100%" src="' + uri + '"/></div>')
    .dialog({
        modal: true,
        title: 'Review Image',
        zIndex: 10000,
        autoOpen: true,
        width: '900',
        height: '600',
        resizable: false,
        buttons: {
            Close: function () {
                $(this).dialog("close");
            }
        }
    });
	
}

    function filterList() {
    var value = $('#filterList').val();
    var list = $('#nav_User_Guide li a');
    for(var i = 0; i < list.length; i++) {
        var item = list[i];
        if(value.length === 0) {
            $(item).parent().show();
        } else {
           if($(item).text().toLowerCase().indexOf(value.toLowerCase()) > -1) {
               $(item).parent().show();
           } else {
               $(item).parent().hide();
           }
        }
    }
   
    
}
var cache = null;
 $(document).on('click', '#editEntryDocument', function(e) {
     showLoader();
       $.ajax({
            type: "GET",
            url: "/doc/get.php?getContentFromId="+current_page_id,
            cache: false,
            success: function(response) {
                console.log(response);
                if(response.result) {
                    cache = response;
                    var html = '<form action="" method="POST" id="updateDocument" class="form">';
                    html += '<input type="hidden" id="id" value="'+current_page_id+'" />';
                    html += '<textarea class="form-control editor" id="editor">'+response.content+'</textarea><hr />';
                    html += '<input type="submit" class="btn btn-default" value="Save Entry" /> &nbsp; <button class="btn btn-default" id="hideEditor">Cancel</button>';
                    html += '</form>';
                    $('.col-md-8 .panel-body').html(html);
                     CKEDITOR.replace( 'editor');
                    hideLoader();
                }
            }
        });
        return false;
    });
$(document).ready(function() {
CKEDITOR.replace( 'editorNew');
    $(document).on('click', '#hideEditor', function(e) {
        var html = cache.content;
        $(".col-md-8 .panel-body").html(html);
        return false;
    });
    
    $(document).on('submit', '#addNewEntryForm', function(e) {
        var data = $("#editorNew").val();
        var entry_parent = $("#entry_parent").val();
        var entry_name = $("#entry_name").val();
        if(entry_parent == '') {
            $("#addNewEntryForm #error").remove();
            $("#addNewEntryForm").prepend('<div id="error" class="alert alert-danger">Please select a parent</div>');
        } else if(entry_name == '') {
            $("#addNewEntryForm #error").remove();
            $("#addNewEntryForm").prepend('<div id="error" class="alert alert-danger">Please enter a name</div>');
        } else {
            showLoader();
            $.ajax({
                type: "POST",
                url: "/doc/update.php",
                data: {parent_id: entry_parent, data: data, entry_name: entry_name},
                cache: false,
                success: function(response) {
                    location.reload();
                }
            });
        }
        return false;
    });
    
    $(document).on('submit', '#updateDocument', function(e) {
       var data = $("#editor").val();
       showLoader();
       $.ajax({
            type: "POST",
            url: "/doc/update.php",
            data: {updatedContent: data, id: current_page_id},
            cache: false,
            success: function(response) {
                console.log(response);
                if(response.result) {
                    cache = response;
                    var html = response.content;
                    $('.col-md-8 .panel-body').html(html);
                    hideLoader();
                }
            }
        });
       return false;
    });
});

function showLoader() {
    $("#loader").fadeIn(300);
}

function hideLoader() {
    $("#loader").fadeOut(300);
}

loadDocGroup('User_Guide');
loadPage(108,"Introduction");

</script>
<?php

function getCategorysOptions() {
    $db = new db_helper();
    $db->CommandText("SELECT document_id, document_name FROM documents;");
    $db->Execute();
    if ($db->Rows_Count() > 0) {
        while( $r = $db->Rows() ) {
            $document_id = $r['document_id'];
            $document_name = $r['document_name'];
            echo '<option value="'.$document_id.'">'.$document_name.'</option>';
        }
    }
}
include_once("lib/footer.php");
?>
