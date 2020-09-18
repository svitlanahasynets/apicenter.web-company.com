<whmcs-pages-component post-title="<?php echo translate('Knowledgebase'); echo isset($catdetail['name']) ? ' / '.$catdetail['name'] : ''; ?>"  inline-template>
    <component :is="layout">
        <page-title :heading=heading :icon=icon></page-title>
        <div>
            <button class="m-aside-left-close m-aside-left-close--skin-light" id="m_aside_left_close_btn">
                <i class="la la-close"></i>
            </button>
            
            <h4 class="d-flex flex-wrap justify-content-between align-items-center mb-3"><div><?php echo translate('Articles') ?></div>
                <div class="col-12 col-md-3 p-0 mb-3"><form action="<?php echo site_url('knowledgebase/search/'); ?>" method="POST">
                        <input type="text" placeholder="Search..." name="keyword" class="form-control"><input type="submit" class="btn btn-primary"  value="Search">
                    </form></div></h4>
           
          <div class="card mb-3">
           
            <?php if( !empty($catarticles) ){ foreach($catarticles as $catarticle){ ?>
                <div class="card-body py-3">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <a href="<?php echo site_url('knowledgebase/article/'.$catarticle['id']);?>" class="text-big"><?php echo $catarticle['title']; ?></a>
                            <div class="text-muted small mt-1"><?php echo strlen($catarticle['article']) > 50 ? substr(strip_tags($catarticle['article']),0,50)."..." : strip_tags($catarticle['article']); ?></div>
                        </div>
                    </div>

                </div>
                <hr class="m-0">
            <?php } }else{ ?>
                 <div class="card-body py-3">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-muted small mt-1">No article found!</div>
                        </div>
                    </div>

                </div>
            <?php } ?>
           
        </div>
            
            
        </div>
    </component>
</whmcs-pages-component>