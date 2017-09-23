<?php
 include_once("lib/lib.php");


if(isset($_GET['getContentFromSlug'])) {
    $slug = $_GET['getContentFromSlug'];
    $db = new db_helper();
    $db->CommandText("SELECT * FROM documents WHERE document_slug = '%s';");
    $db->Parameters($slug);
    $db->Execute();
    $content = '';
    if ($db->Rows_Count() > 0) {
        while( $r = $db->Rows() ) {
            $content = $r['document_body'];
        }
    }
    
    header('Content-type: text/json');
    echo '{"result":true,"content":'.json_encode($content).'}';
    return;
} else {
   
    include_once("lib/header.php");


if(form('addCategory')) {
    $category_name = form('category_name');
    if(empty($category_name)) {
       // empty
    } else {
        $category_slug = strtolower(generateSlug($category_name));
        addCategory($category_name, $category_slug);
        $category = '';
        $document = '';
        if(isset($_GET['category_slug'])) {
            $category = $_GET['category_slug'];
        }
        if(isset($_GET['document_slug'])) {
            $document = $_GET['document_slug'];
            $document = explode('.', $document);
            $document = $document[0];
        }
        if(!empty($category) && !empty($document)) {
            header("Location: /documentation/".$category."/".$document."?added=category");
            exit();
        } else if(!empty($category) && empty($document)) {
            header("Location: /documentation/".$category."?added=category");
            exit();
        } else {
            header("Location: /documentation/?added=category");
            exit();
        }
    }
}

if(form('addEntry')) {
    $entry_name = form('entry_name');
    $entry_category = form('entry_category');
    $entry_content = form('entry_content');
    $entry_slug = strtolower(generateSlug($entry_name));
    if(empty($entry_name) || empty($entry_category) || empty($entry_content)) {
        // empty
    } else {
         addEntry($entry_name, $entry_slug, $entry_category, $entry_content);
       
         $category = '';
        $document = '';
        if(isset($_GET['category_slug'])) {
            $category = $_GET['category_slug'];
        }
        if(isset($_GET['document_slug'])) {
            $document = $_GET['document_slug'];
            $document = explode('.', $document);
            $document = $document[0];
        }
        
        if(!empty($category) && !empty($document)) {
            header("Location: /documentation/".$category."/".$document."?added=document");
            exit();
        } else if(!empty($category) && empty($document)) {
            header("Location: /documentation/".$category."?added=document");
            exit();
        } else {
            header("Location: /documentation/?added=document");
            exit();
        }
        
    }
}

$document = '';
if(isset($_GET['document_slug'])) {
    $document = $_GET['document_slug'];
    $document = explode('.', $document);
    $document = $document[0];
}
?>
    	<!--external css-->
    	<link rel="stylesheet" type="text/css" href="/js/fuelux/css/tree-style.css" />
    	 <link rel="stylesheet" type="text/css" href="/js/bootstrap-wysihtml5/bootstrap-wysihtml5.css" />
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
		</style>
		
<?php
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
                <?php if(!empty($document)):?><li><a id="editEntryDocument" data-slug="<?php echo $document;?>">Edit</a></li><?php endif; ?>
                <li class="dropdown">
                        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">New <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a data-toggle="modal" data-target="#addNewCategory">Add New Category</a></li>
                            <li><a data-toggle="modal" data-target="#addNewEntry">Add New Entry</a></li>
                        </ul>
                </li>
            </ul>
    </div><!-- /.navbar-collapse -->
</nav>
    <?php endif; ?>
    <div class="col-md-3">
        <div class="panel panel-default" id="sideAffix" >
            <div class="panel-heading">
                <div class="clearfix">
                    <div class="pull-left"><h4 style="padding: 0;margin: 0;position: relative;top: 8px;">Entries</h4></div>
                    <div class="pull-right"><input type="text" id="filterList" class="form-control" onkeyup="filterList();" placeholder="Search for..."/></div>
                </div>
            </div>
            <div class="panel-body" style="padding: 0px; height: 450px; display: block; overflow-y: scroll;">
                <?php echo documentCategories();?>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="panel panel-default">
            
            <div class="panel-body">
                <div id="entryContent">
                    <?php echo showActiveDocument();?>
                </div>
            </div>
        </div>
    </div>
</div>
             
                
<?php
include_once("lib/body_end.php");
?>
<?php if(session("client_id") == 1):?>
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
          <form class="form" action="" method="POST">
              <div class="form-group">
                  <label>Entry Name</label>
                  <input type="text" class="form-control" name="entry_name" placeholder="Entry Name" />
              </div>
              
              <div class="form-group">
                  <label>Entry Category</label>
                  <select class="form-control" name="entry_category">
                      <option value disabled>-- Select Category --</option>
                      <?php echo getCategorysOptions();?>
                  </select>
              </div>
              
              <div class="form-group">
                  <label>Entry Content</label>
                  <textarea class="wysihtml5 form-control" name="entry_content" rows="10"></textarea>
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
            <?php endif; ?>    
<script type="text/javascript" src="/js/bootstrap-wysihtml5/wysihtml5-0.3.0.js"></script>
<script type="text/javascript" src="/js/bootstrap-wysihtml5/bootstrap-wysihtml5.js"></script>
<script type="text/javascript" src="/js/ckeditor/ckeditor.js"></script>
<script>
    $(function(){
        $('.wysihtml5').wysihtml5({"stylesheets": false, "id":"changeLogContent"});
    });
    
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
     var slug = $(this).data('slug');
     showLoader();
       $.ajax({
            type: "GET",
            url: "/documentation?getContentFromSlug="+slug,
            cache: false,
            success: function(response) {
                console.log(response);
                if(response.result) {
                    cache = response;
                    var html = '<form action="" method="POST" id="updateDocument" class="form">';
                    html += '<input type="hidden" id="slug" value="'+slug+'" />';
                    html += '<textarea class="form-control editor" id="editor">'+response.content+'</textarea><hr />';
                    html += '<input type="submit" class="btn btn-default" value="Save Entry" /> &nbsp; <button class="btn btn-default" id="hideEditor">Cancel</button>';
                    html += '</form>';
                    $('#entryContent').html(html);
                     CKEDITOR.replace( 'editor');
                    hideLoader();
                }
            }
        });
    });
$(document).ready(function() {
    $(document).on('click', '#hideEditor', function(e) {
        var html = cache.content;
        $("#entryContent").html(html);
        return false;
    });
    
    $(document).on('submit', '#updateDocument', function(e) {
       var data = $("#editor").val();
       var slug = getParameterByName("document_slug");
       showLoader();
       $.ajax({
            type: "POST",
            url: "/doc/update.php",
            data: {updatedContent: data, slug: slug},
            cache: false,
            success: function(response) {
                console.log(response);
                if(response.result) {
                    cache = response;
                    var html = response.content;
                    $('#entryContent').html(html);
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
</script>
<style type="text/css">
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
</style>
<?php
include_once("lib/footer.php");
?>

<?php 
}
$category = '';
$document = '';
if(isset($_GET['category'])) {
    $category = $_GET['category'];
}

if(isset($_GET['document'])) {
    $document = $_GET['document'];
}

function getCategorysOptions() {
    $db = new db_helper();
    $db->CommandText("SELECT * FROM documents_category;");
    $db->Execute();
    if ($db->Rows_Count() > 0) {
        while( $r = $db->Rows() ) {
            $category_id = $r['category_id'];
            $category_name = $r['category_name'];
            echo '<option value="'.$category_id.'">'.$category_name.'</option>';
        }
    }
}
function documentCategories() {
    $category = '';
    if(isset($_GET['category_slug'])) {
        $category = $_GET['category_slug'];
       
    }
    $db = new db_helper();
    $db->CommandText("SELECT * FROM documents_category;");
    $db->Execute();
    echo '<ul id="nav_User_Guide" class="nav nav-pills nav-stacked mail-nav" style="margin-left: 0px; margin-top: 0px; margin-right: 0px; display: block;">';
    if ($db->Rows_Count() > 0) {
        while( $r = $db->Rows() ) {
            $category_id = $r['category_id'];
            $category_name = $r['category_name'];
            $category_slug = $r['category_slug'];
            $selected = '';
            if(strtolower($category) == strtolower($category_slug)) {
                $selected = 'active';
            }
            echo '<li class="panel-heading '.$selected.'"><strong>'.$category_name.'</strong></li>';
            getCategoryChildren($category_id, $category_slug);
        }
    }
    echo '</ul>';
}

function getCategoryChildren($category_id, $category_slug) {
    $document = '';
    if(isset($_GET['document_slug'])) {
        $document = $_GET['document_slug'];
        $document = explode('.', $document);
        $document = $document[0];
    }
    $db = new db_helper();
    $db->CommandText("SELECT * FROM documents WHERE document_category_id = '%s';");
    $db->Parameters($category_id);
    $db->Execute();
    if ($db->Rows_Count() > 0) {
        while( $r = $db->Rows() ) {
            $document_name = $r['document_name'];
            $document_slug = $r['document_slug'];
            $selected = '';
            if(strtolower($document) == strtolower($document_slug)) {
                if($document_slug != '') {
                    $selected = 'active';
                }
            }
            echo '<li class="'.$selected.'"><a href="/documentation/'.$category_slug.'/'.$document_slug.'">'.$document_name.'</a></li>';
        }
    }
}

function showActiveDocument() {
    $document = '';
    if(isset($_GET['document_slug'])) {
        $document = $_GET['document_slug'];
        $document = explode('.', $document);
        $document = $document[0];
    }
    
    if(!empty($document)) {
        $db = new db_helper();
        $db->CommandText("SELECT * FROM documents WHERE document_slug = '%s';");
        $db->Parameters($document);
        $db->Execute();
        
        if ($db->Rows_Count() > 0) {
            while( $r = $db->Rows() ) {
                $document_body = $r['document_body'];
                $document_name = $r['document_name'];
                echo '<h3>'.$document_name.'</h3><hr />';
                echo $document_body;
            }
        }
    }
}

function addCategory($category_name, $category_slug) {
    $db = new db_helper();
    $db->CommandText("INSERT INTO documents_category (category_name, category_slug) VALUES('%s','%s');");
    $db->Parameters($category_name);
    $db->Parameters($category_slug);
    $db->Execute();
}

function addEntry($entry_name, $entry_slug, $entry_category, $entry_content) {
    $db = new db_helper();
    $db->CommandText("INSERT INTO documents (document_name, document_slug, document_category_id, document_body) VALUES('%s','%s', '%s', '%s');");
    $db->Parameters($entry_name);
    $db->Parameters($entry_slug);
    $db->Parameters($entry_category);
    $db->Parameters($entry_content);
    $db->Execute();
}

function generateSlug($string){
   $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
   return $slug;
}

