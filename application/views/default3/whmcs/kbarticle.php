<whmcs-pages-component post-title="<?php echo translate('Knowledgebase'); echo isset($article['title']) ? ' / '.$article['title'] : ''; ?>"  inline-template>
    <component :is="layout">
        <page-title :heading=heading :icon=icon></page-title>
        <div>
            <button class="m-aside-left-close m-aside-left-close--skin-light" id="m_aside_left_close_btn">
                <i class="la la-close"></i>
            </button>
            
            <h4 class="d-flex flex-wrap justify-content-between align-items-center mb-3"><div><?php echo $article['title'] ?></div><div class="col-12 col-md-3 p-0 mb-3"></div></h4>
           
          <div class="card mb-3">
           
           
                <div class="card-body py-3">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-muted small mt-1"><?php echo $article['article']; ?></div>
                        </div>
                    </div>

                </div>
                <hr class="m-0">
          
           
        </div>
            
            
        </div>
    </component>
</whmcs-pages-component>