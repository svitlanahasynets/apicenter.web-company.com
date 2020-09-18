<whmcs-pages-component post-title="<?php echo translate('Knowledgebase') ?>"  inline-template>
    <component :is="layout">
        <page-title :heading=heading :icon=icon></page-title>
        <div>
            <button class="m-aside-left-close m-aside-left-close--skin-light" id="m_aside_left_close_btn">
                <i class="la la-close"></i>
            </button>

            <h4 class="d-flex flex-wrap justify-content-between align-items-center mb-3"><div><?php echo translate('Categories') ?></div>
                <div class="col-12 col-md-3 p-0 mb-3"><form action="<?php echo site_url('knowledgebase/search/'); ?>" method="POST">
                        <input type="text" name="keyword" placeholder="Search..." class="form-control"><input type="submit" class="btn btn-primary" value="Search">
                    </form></div></h4>
            <div class="card mb-3">
                
                    <?php if( !empty($cats) ){ foreach($cats as $cat){ ?>
                      <div class="kb-cat-box">
                        <div class="card-body py-3">

                                <div class="row no-gutters align-items-center">
                                    <div class="col">
                                        <a href="<?php echo site_url('knowledgebase/cats/'.$cat['id']);?>" class="text-big font-weight-semibold"><?php echo $cat['name']; ?></a>
                                        <div class="text-muted small mt-1"><?php echo $cat['description']; ?> </div>
                                    </div>
                                </div>

                            </div>
                            <hr class="m-0">
                        </div>
                    <?php } }else{ ?>
                         <div class="kb-cat-box">
                            <div class="card-body py-3">

                                    <div class="row no-gutters align-items-center">
                                        <div class="col">
                                            <div class="text-muted small mt-1">No category found!</div>
                                        </div>
                                    </div>

                                </div>
                            <hr class="m-0">
                        </div>
                    <?php } ?>
            </div>
            
            
        </div>
    </component>
</whmcs-pages-component>