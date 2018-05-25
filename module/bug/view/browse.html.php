<?php
/**
 * The browse view file of bug module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     bug
 * @version     $Id: browse.html.php 5102 2013-07-12 00:59:54Z chencongzhi520@gmail.com $
 * @link        http://www.zentao.net
 */
?>
<?php include '../../common/view/header.html.php';?>
<?php
include '../../common/view/datatable.fix.html.php';
js::set('browseType',    $browseType);
js::set('moduleID',      $moduleID);
js::set('bugBrowseType', ($browseType == 'bymodule' and $this->session->bugBrowseType == 'bysearch') ? 'all' : $this->session->bugBrowseType);
js::set('flow', $this->config->global->flow);
js::set('productID', $productID);
js::set('branch', $branch);
$currentBrowseType = isset($lang->bug->mySelects[$browseType]) && in_array($browseType, array_keys($lang->bug->mySelects)) ? $browseType : '';
?>
<div id="mainMenu" class="clearfix">
  <?php if($this->config->global->flow == 'onlyTest'):?>
  <div id='featurebar'>
    <ul class='submenu hidden'>
      <li id='moreMenus' class='hidden'>
        <a href='###' class='dropdown-toggle' data-toggle='dropdown'>
          <?php echo $lang->more;?> <span class='caret'></span>
        </a>
        <ul class='dropdown-menu right'>
        </ul>
      </li>
      <li id='bysearchTab'><a href='#'><i class='icon-search icon'></i>&nbsp;<?php echo $lang->bug->byQuery;?></a></li>
      <li class='right'>
        <div class='btn-group' id='createActionMenu'>
          <?php
          $misc = common::hasPriv('bug', 'create') ? "class='btn btn-primary'" : "class='btn btn-primary disabled'";
          $link = common::hasPriv('bug', 'create') ?  $this->createLink('bug', 'create', "productID=$productID&branch=$branch&extra=moduleID=$moduleID") : '#';
          echo html::a($link, "<i class='icon icon-plus'></i>" . $lang->bug->create, '', $misc);

          $misc = common::hasPriv('bug', 'batchCreate') ? '' : "disabled";
          $link = common::hasPriv('bug', 'batchCreate') ?  $this->createLink('bug', 'batchCreate', "productID=$productID&branch=$branch&projectID=0&moduleID=$moduleID") : '#';
          ?>
          <button type='button' class='btn btn-primary dropdown-toggle <?php echo $misc?>' data-toggle='dropdown'>
            <span class='caret'></span>
          </button>
          <ul class='dropdown-menu right'>
          <?php echo "<li>" . html::a($link, $lang->bug->batchCreate, '', "class='$misc'") . "</li>";?>
          </ul>
        </div>
      </li>
      <li class='right'>
        <?php common::printLink('bug', 'report', "productID=$productID&browseType=$browseType&branchID=$branch&moduleID=$moduleID", "<i class='icon icon-bar-chart muted'></i> " . $lang->bug->report->common); ?>
      </li>
      <li class='right'>
        <a href='###' class='dropdown-toggle' data-toggle='dropdown'>
          <i class='icon-download-alt'></i> <?php echo $lang->export ?>
          <span class='caret'></span>
        </a>
        <ul class='dropdown-menu' id='exportActionMenu'>
          <?php
          $misc = common::hasPriv('bug', 'export') ? "class='export'" : "class=disabled";
          $link = common::hasPriv('bug', 'export') ?  $this->createLink('bug', 'export', "productID=$productID&orderBy=$orderBy") : '#';
          echo "<li>" . html::a($link, $lang->bug->export, '', $misc) . "</li>";
          ?>
        </ul>
      </li>
    </ul>
    <div id='querybox' class='<?php if($browseType =='bysearch') echo 'show';?>'></div>
  </div>
  <?php else:?>
  <div id="sidebarHeader">
    <div class="title">
      <?php
      echo $moduleName;
      if($moduleID)
      {
          $removeLink = $browseType == 'bymodule' ? inlink('browse', "productID=$productID&branch=$branch&browseType=$browseType&param=0&orderBy=$orderBy&recTotal=0&recPerPage={$pager->recPerPage}") : 'javascript:removeCookieByKey("bugModule")';
          echo html::a($removeLink, "<i class='icon icon-sm icon-close'></i>", '', "class='text-muted'");
      }
      ?>
    </div>
  </div>
  <div class="btn-toolbar pull-left">
    <?php
    $menus           = customModel::getFeatureMenu($this->moduleName, $this->methodName);
    $moreLabel       = $lang->more;
    $moreLabelActive = '';
    if(strpos(',unconfirmed,assigntonull,longlifebugs,postponedbugs,overduebugs,needconfirm,', $browseType) !== false)
    {
        foreach($menus as $menuItem)
        {
            if($menuItem->name == $browseType)
            {
                $moreLabel       = "<span class='text'>{$menuItem->text}</span><span class='label label-light label-badge'>{$pager->recTotal}</span>";
                $moreLabelActive = 'btn-active-text';
            }
        }
    }
    foreach($menus as $menuItem)
    {
        if(isset($menuItem->hidden)) continue;
        if($this->config->global->flow == 'onlyTest' and $menuItem->name == 'needconfirm') continue;

        $menuBrowseType = strpos($menuItem->name, 'QUERY') === 0 ? 'bySearch' : $menuItem->name;
        $barParam       = strpos($menuItem->name, 'QUERY') === 0 ? (int)substr($menuItem->name, 5) : 0;
        $label          = "<span class='text'>{$menuItem->text}</span>";
        $label         .= $menuBrowseType == $browseType ? "<span class='label label-light label-badge'>{$pager->recTotal}</span>" : '';
        $active         = $menuBrowseType == $browseType ? 'btn-active-text' : '';

        if($menuItem->name == 'my')
        {
            echo "<li id='statusTab' class='dropdown " . (!empty($currentBrowseType) ? 'active' : '') . "'>";
            echo html::a('javascript:;', $menuItem->text . " <span class='caret'></span>", '', "data-toggle='dropdown' class='btn btn-link'");
            echo "<ul class='dropdown-menu'>";
            foreach($lang->bug->mySelects as $key => $value)
            {
                echo '<li' . ($key == $currentBrowseType ? " class='active'" : '') . '>';
                echo html::a($this->createLink('bug', 'browse', "productid=$productID&branch=$branch&browseType=$key&param=$barParam"), $value);
            }
            echo '</ul></li>';
        }
        else
        {
            if(strpos(',unconfirmed,assigntonull,longlifebugs,postponedbugs,overduebugs,needconfirm,', $menuItem->name) !== false)
            {
                if($menuItem->name == 'unconfirmed')
                {
                    echo "<div class='btn-group'><a href='javascript:;' data-toggle='dropdown' class='btn btn-link {$moreLabelActive}'>{$moreLabel} <span class='caret'></span></a>";
                    echo "<ul class='dropdown-menu'>";
                }
                echo '<li>' . html::a($this->createLink('bug', 'browse', "productid=$productID&branch=$branch&browseType=$menuBrowseType&param=$barParam"), "<span class='text'>{$menuItem->text}</span>", '', "class='btn btn-link $active'") . '</li>';
                if($menuItem->name == 'needconfirm') echo '</ul></div>';
            }
            else
            {
                echo html::a($this->createLink('bug', 'browse', "productid=$productID&branch=$branch&browseType=$menuBrowseType&param=$barParam"), $label, '', "class='btn btn-link $active'");
            }
        }
    }
    ?>
    <a class="btn btn-link querybox-toggle" id='bysearchTab'><i class="icon icon-search muted"></i> <?php echo $lang->bug->byQuery;?></a>
  </div>
  <div class="btn-toolbar pull-right">
    <?php common::printIcon('bug', 'report', "productID=$productID&browseType=$browseType&branchID=$branch&moduleID=$moduleID", '', 'button', 'bar-chart muted');?>
    <?php if(common::hasPriv('bug', 'export')):?>
    <div class='btn-group'>
      <button type='button' class='btn btn-link dropdown-toggle' data-toggle='dropdown'>
        <i class="icon icon-export muted"></i> <span class="text"> <?php echo $lang->export;?></span> <span class="caret"></span></button>
      </button>
      <ul class='dropdown-menu' id='exportActionMenu'>
        <?php
        $link = $this->createLink('bug', 'export', "productID=$productID&orderBy=$orderBy");
        echo "<li>" . html::a($link, $lang->bug->export, '', "class='export'") . "</li>";
        ?>
      </ul>
    </div>
    <?php endif;?>
    <?php
    common::printLink('bug', 'batchCreate', "productID=$productID&branch=$branch&projectID=0&moduleID=$moduleID", "<i class='icon icon-plus'></i>" . $lang->bug->batchCreate, '', "class='btn btn-secondary'");
    if(commonModel::isTutorialMode())
    {
        $wizardParams = helper::safe64Encode("productID=$productID&branch=$branch&extra=moduleID=$moduleID");
        echo html::a($this->createLink('tutorial', 'wizard', "module=bug&method=create&params=$wizardParams"), "<i class='icon-plus'></i>" . $lang->bug->create, '', "class='btn btn-primary btn-bug-create'");
    }
    else
    {
        common::printLink('bug', 'create', "productID=$productID&branch=$branch&extra=moduleID=$moduleID", "<i class='icon icon-plus'></i>" . $lang->bug->create, '', "class='btn btn-primary'");
    }
    ?>
  </div>
  <?php endif;?>
</div>
<div id="mainContent" class="main-row">
  <div class="side-col" id="sidebar">
    <div class="sidebar-toggle"><i class="icon icon-angle-left"></i></div>
    <div class="cell">
      <?php if(!$moduleTree):?>
      <hr class="space">
      <div class="text-center text-muted"><?php echo $lang->bug->noModule;?></div>
      <hr class="space">
      <?php endif;?>
      <?php echo $moduleTree;?>
      <div class="text-center">
        <?php common::printLink('tree', 'browse', "productID=$productID&view=bug", $lang->tree->manage, '', "class='btn btn-info btn-wide'");?>
        <hr class="space-sm" />
      </div>
    </div>
  </div>
  <div class="main-col">
    <div class="cell<?php if($browseType == 'bysearch') echo ' show';?>" id="queryBox"></div>
    <form class='main-table table-bug' data-ride='table' method='post' id='bugForm'>
      <div class="table-header fixed-right">
        <nav class="btn-toolbar pull-right"></nav>
      </div>
      <?php
      $datatableId  = $this->moduleName . ucfirst($this->methodName);
      $useDatatable = (isset($this->config->datatable->$datatableId->mode) and $this->config->datatable->$datatableId->mode == 'datatable');
      $vars         = "productID=$productID&branch=$branch&browseType=$browseType&param=$param&orderBy=%s&recTotal={$pager->recTotal}&recPerPage={$pager->recPerPage}";
      if($useDatatable) include '../../common/view/datatable.html.php';

      $setting = $this->datatable->getSetting('bug');
      $widths  = $this->datatable->setFixedFieldWidth($setting);
      $columns = 0;
      ?>
      <table class='table has-sort-head' id='bugList'>
        <thead>
          <tr>
          <?php
          foreach($setting as $key => $value)
          {
              if($value->show)
              {
                  $this->datatable->printHead($value, $orderBy, $vars);
                  $columns ++;
              }
          }
          ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach($bugs as $bug):?>
          <tr data-id='<?php echo $bug->id?>'>
            <?php foreach($setting as $key => $value) $this->bug->printCell($value, $bug, $users, $builds, $branches, $modulePairs, $projects, $plans, $stories, $tasks, $useDatatable ? 'datatable' : 'table');?>
          </tr>
          <?php endforeach;?>
        </tbody>
      </table>
      <?php if(!empty($bugs)):?>
      <div class='table-footer'>
        <div class="checkbox-primary check-all"><label><?php echo $lang->selectAll?></label></div>
        <div class="table-actions btn-toolbar">
          <div class='btn-group dropup'>
            <?php
            $actionLink = $this->createLink('bug', 'batchEdit', "productID=$productID&branch=$branch");
            $misc       = common::hasPriv('bug', 'batchEdit') ? "onclick=\"setFormAction('$actionLink')\"" : "disabled='disabled'";
            echo html::commonButton($lang->edit, $misc);
            ?>
            <button type='button' class='btn dropdown-toggle' data-toggle='dropdown'><span class='caret'></span></button>
            <ul class='dropdown-menu'>
              <?php
              $class = "class='disabled'";
              $actionLink = $this->createLink('bug', 'batchConfirm');
              $misc = common::hasPriv('bug', 'batchConfirm') ? "onclick=\"setFormAction('$actionLink', 'hiddenwin')\"" : $class;
              if($misc) echo "<li>" . html::a('javascript:;', $lang->bug->confirmBug, '', $misc) . "</li>";

              $actionLink = $this->createLink('bug', 'batchClose');
              $misc = common::hasPriv('bug', 'batchClose') ? "onclick=\"setFormAction('$actionLink', 'hiddenwin')\"" : $class;
              if($misc) echo "<li>" . html::a('javascript:;', $lang->bug->close, '', $misc) . "</li>";

              $actionLink = $this->createLink('bug', 'batchActivate', "productID=$productID&branch=$branch");
              $misc = common::hasPriv('bug', 'batchActivate') ? "onclick=\"setFormAction('$actionLink')\"" : $class;
              if($misc) echo "<li>" . html::a('javascript:;', $lang->bug->activate, '', $misc) . "</li>";

              $misc = common::hasPriv('bug', 'batchResolve') ? "id='resolveItem'" : '';
              if($misc)
              {
                  echo "<li class='dropdown-submenu'>" . html::a('javascript:;', $lang->bug->resolve,  '', $misc);
                  echo "<ul class='dropdown-menu'>";
                  unset($lang->bug->resolutionList['']);
                  unset($lang->bug->resolutionList['duplicate']);
                  unset($lang->bug->resolutionList['tostory']);
                  foreach($lang->bug->resolutionList as $key => $resolution)
                  {
                      $actionLink = $this->createLink('bug', 'batchResolve', "resolution=$key");
                      if($key == 'fixed')
                      {
                          $withSearch = count($builds) > 4;
                          echo "<li class='dropdown-submenu'>";
                          echo html::a('javascript:;', $resolution, '', "id='fixedItem'");
                          echo "<div class='dropdown-menu" . ($withSearch ? ' with-search':'') . "'>";
                          echo '<ul class="dropdown-list">';
                          unset($builds['']);
                          foreach($builds as $key => $build)
                          {
                              $actionLink = $this->createLink('bug', 'batchResolve', "resolution=fixed&resolvedBuild=$key");
                              echo "<li class='option' data-key='$key'>";
                              echo html::a('javascript:;', $build, '', "onclick=\"setFormAction('$actionLink','hiddenwin')\"");
                              echo "</li>";
                          }
                          echo "</ul>";
                          if($withSearch) echo "<div class='menu-search'><div class='input-group input-group-sm'><input type='text' class='form-control' placeholder=''><span class='input-group-addon'><i class='icon-search'></i></span></div></div>";
                          echo '</div></li>';
                      }
                      else
                      {
                          echo '<li>' . html::a('javascript:;', $resolution, '', "onclick=\"setFormAction('$actionLink','hiddenwin')\"") . '</li>';
                      }
                  }
                  echo '</ul></li>';
              }
              ?>
            </ul>
          </div>
          <?php if(common::hasPriv('bug', 'batchChangeBranch') and $this->session->currentProductType != 'normal'):?>
          <div class="btn-group dropup">
            <button data-toggle="dropdown" type="button" class="btn"><?php echo $lang->product->branchName[$this->session->currentProductType];?> <span class="caret"></span></button>
            <?php $withSearch = count($branches) > 8;?>
            <?php if($withSearch):?>
            <div class="dropdown-menu search-list" data-ride="searchList">
              <div class="input-control search-box search-box-circle has-icon-left has-icon-right search-example">
                <input id="userSearchBox" type="search" autocomplete="off" class="form-control search-input">
                <label for="userSearchBox" class="input-control-icon-left search-icon"><i class="icon icon-search"></i></label>
                <a class="input-control-icon-right search-clear-btn"><i class="icon icon-close icon-sm"></i></a>
              </div>
            <?php else:?>
            <div class="dropdown-menu">
            <?php endif;?>
              <div class="list-group">
                <?php
                foreach($branches as $branchID => $branchName)
                {
                    $actionLink = $this->createLink('bug', 'batchChangeBranch', "branchID=$branchID");
                    echo html::a('#', $branchName, '', "onclick=\"setFormAction('$actionLink', 'hiddenwin')\" data-key='$branchID'");
                }
                ?>
              </div>
            </div>
          </div>
          <?php endif;?>
          <?php if(common::hasPriv('bug', 'batchChangeModule')):?>
          <div class="btn-group dropup">
            <button data-toggle="dropdown" type="button" class="btn"><?php echo $lang->bug->moduleAB;?> <span class="caret"></span></button>
            <?php $withSearch = count($modules) > 8;?>
            <?php if($withSearch):?>
            <div class="dropdown-menu search-list" data-ride="searchList">
              <div class="input-control search-box search-box-circle has-icon-left has-icon-right search-example">
                <input id="userSearchBox" type="search" autocomplete="off" class="form-control search-input">
                <label for="userSearchBox" class="input-control-icon-left search-icon"><i class="icon icon-search"></i></label>
                <a class="input-control-icon-right search-clear-btn"><i class="icon icon-close icon-sm"></i></a>
              </div>
            <?php else:?>
            <div class="dropdown-menu">
            <?php endif;?>
              <div class="list-group">
                <?php
                foreach($modules as $moduleId => $module)
                {
                    $actionLink = $this->createLink('bug', 'batchChangeModule', "moduleID=$moduleId");
                    echo html::a('#', $module, '', "onclick=\"setFormAction('$actionLink','hiddenwin')\" data-key='$moduleID'");
                }
                ?>
              </div>
            </div>
          </div>
          <?php endif;?>
          <?php if(common::hasPriv('bug', 'batchAssignTo')):?>
          <div class="btn-group dropup">
            <button data-toggle="dropdown" type="button" class="btn"><?php echo $lang->bug->assignedTo;?> <span class="caret"></span></button>
            <?php $withSearch = count($memberPairs) > 10;?>
            <?php if($withSearch):?>
            <div class="dropdown-menu search-list" data-ride="searchList">
              <div class="input-control search-box search-box-circle has-icon-left has-icon-right search-example">
                <input id="userSearchBox" type="search" autocomplete="off" class="form-control search-input">
                <label for="userSearchBox" class="input-control-icon-left search-icon"><i class="icon icon-search"></i></label>
                <a class="input-control-icon-right search-clear-btn"><i class="icon icon-close icon-sm"></i></a>
              </div>
            <?php else:?>
            <div class="dropdown-menu">
            <?php endif;?>
              <div class="list-group">
                <?php
                $actionLink = $this->createLink('bug', 'batchAssignTo', "productID={$productID}&type=product");
                echo html::select('assignedTo', $memberPairs, '', 'class="hidden"');
                foreach ($memberPairs as $key => $value)
                {
                    if(empty($key)) continue;
                    echo html::a("javascript:$(\"#assignedTo\").val(\"$key\");setFormAction(\"$actionLink\",\"hiddenwin\")", $value, '', "data-key='$key'");
                }
                ?>
              </div>
            </div>
          </div>
          <?php endif;?>
        </div>
        <div class="table-statistic"><?php echo $summary;?></div>
        <?php $pager->show('right', 'pagerjs');?>
      </div>
      <?php elseif(common::hasPriv('bug', 'create')):?>
      <div class="table-empty-tip">
        <p><span class="text-muted"><?php echo $lang->bug->noBug;?></span> <?php common::printLink('bug', 'create', "productID={$productID}", "<i class='icon icon-plus'></i> " . $lang->bug->create, '', "class='btn btn-info'");?></p>
      </div>
      <?php endif;?>
    </form>
  </div>
</div>
<script>
$('#' + bugBrowseType + 'Tab').addClass('active');
$('#module' + moduleID).addClass('active');
<?php if($browseType == 'bysearch'):?>
$shortcut = $('#QUERY<?php echo (int)$param;?>Tab');
if($shortcut.size() > 0)
{
    $shortcut.addClass('active');
    $('#bysearchTab').removeClass('active');
    $('#querybox').removeClass('show');
}
<?php endif;?>
<?php $this->app->loadConfig('qa', '', false);?>
<?php if(isset($config->qa->homepage) and $config->qa->homepage != 'browse' and $config->global->flow == 'full'):?>
$(function(){$('#modulemenu .nav li:last').after("<li class='right'><a style='font-size:12px' href='javascript:setHomepage(\"qa\", \"browse\")'><i class='icon icon-cog'></i> <?php echo $lang->homepage?></a></li>")});
<?php endif;?>
</script>
<?php if($config->global->flow == 'onlyTest'):?>
<style>
.nav > li > .btn-group > a, .nav > li > .btn-group > a:hover, .nav > li > .btn-group > a:focus{background: #1a4f85; border-color: #164270;}
.outer.with-side #featurebar {background: none; border: none; line-height: 0; margin: 0; min-height: 0; padding: 0; }
#querybox #searchform{border-bottom: 1px solid #ddd; margin-bottom: 20px;}
</style>
<?php endif;?>
<?php include '../../common/view/footer.html.php';?>
