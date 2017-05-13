<?php


$installer = $this;
$installer->startSetup();
$installer->endSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute('catalog_product', 'magikfeatured', array(
        'group'             => 'General',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Featured Product On Home',
        'input'             => 'boolean',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => true,
        'default'           => '0',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => 'simple,configurable,virtual,bundle,downloadable',
        'is_configurable'   => false
    )); 

try {
//create pages and blocks programmatically

//Custom Tab1
$staticBlock = array(
    'title' => 'Custom Tab1',
    'identifier' => 'innova_custom_tab1',
    'content' => "<p><strong>Lorem Ipsum</strong><span>&nbsp;is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</span></p>",
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();

//Custom Tab2
$staticBlock = array(
    'title' => 'Custom Tab2',
    'identifier' => 'innova_custom_tab2',
    'content' => "<p><strong>Lorem Ipsum</strong><span>&nbsp;is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</span></p>",
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();

//Custom Block
$staticBlock = array(
    'title' => 'Custom',
    'identifier' => 'innova_navigation_block',
    'content' => "<div>{{block type=\"catalog/product\" num_products=\"4\" name=\"randomproducts\" as=\"randomproducts\" template=\"catalog/random.phtml\" }}</div>",
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();

//Empty Category
$staticBlock = array(
    'title' => 'Empty Category',
    'identifier' => 'innova_empty_category',
    'content' => "<p>There are no products matching the selection.<br/> This is a static CMS block displayed if category is empty. You can put your own content here.</p>",
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();

//Innova Logo Brand block
 $staticBlock = array(
     'title' => 'Innova Logo Brand block',
     'identifier' => 'innova_logo_brand_block',
     'content' => '<div class="brand-logo">
<div class="jcarousel-skin-tango">
<div class="new_title center">
<h2><span>Logo Brands</span></h2>
</div>
<div id="mycarousel2" class="jcarousel-container jcarousel-container-horizontal">
<div class="jcarousel-clip jcarousel-clip-horizontal">
<ul class="jcarousel-list jcarousel-list-horizontal">
<li><img src="{{skin url="images/b-logo1.png"}}" alt="b-logo1.png" /></li>
<li><img src="{{skin url="images/b-logo2.png"}}" alt="b-logo2.png" /></li>
<li><img src="{{skin url="images/b-logo3.png"}}" alt="b-logo3.png" /></li>
<li><img src="{{skin url="images/b-logo4.png"}}" alt="b-logo4.png" /></li>
<li><img src="{{skin url="images/b-logo5.png"}}" alt="b-logo5.png" /></li>
<li><img src="{{skin url="images/b-logo6.png"}}" alt="b-logo6.png" /></li>
<li><img src="{{skin url="images/b-logo3.png"}}" alt="b-logo3.png" /></li>
<li><img src="{{skin url="images/b-logo2.png"}}" alt="b-logo2.png" /></li>
<li><img src="{{skin url="images/b-logo5.png"}}" alt="b-logo5.png" /></li>
<li><img src="{{skin url="images/b-logo4.png"}}" alt="b-logo4.png" /></li>
<li><img src="{{skin url="images/b-logo1.png"}}" alt="b-logo1.png" /></li>
<li><img src="{{skin url="images/b-logo2.png"}}" alt="b-logo2.png" /></li>
<li><img src="{{skin url="images/b-logo3.png"}}" alt="b-logo3.png" /></li>
<li><img src="{{skin url="images/b-logo4.png"}}" alt="b-logo4.png" /></li>
<li><img src="{{skin url="images/b-logo5.png"}}" alt="b-logo5.png" /></li>
<li><img src="{{skin url="images/b-logo6.png"}}" alt="b-logo6.png" /></li>
<li><img src="{{skin url="images/b-logo3.png"}}" alt="b-logo3.png" /></li>
<li><img src="{{skin url="images/b-logo2.png"}}" alt="b-logo2.png" /></li>
<li><img src="{{skin url="images/b-logo5.png"}}" alt="b-logo5.png" /></li>
<li><img src="{{skin url="images/b-logo4.png"}}" alt="b-logo4.png" /></li>
</ul>
</div>
<div class="jcarousel-prev jcarousel-prev-horizontal" style="display: block;">&nbsp;</div>
<div class="jcarousel-next jcarousel-next-horizontal" style="display: block;">&nbsp;</div>
</div>
</div>
</div>',
     'is_active' => 1,
     'stores' => array(0)
 );
 Mage::getModel('cms/block')->setData($staticBlock)->save();

//Innova Store Logo
$staticBlock = array(
    'title' => 'Innova Store Logo',
    'identifier' => 'innova_logo',
    'content' => '<img src="{{skin url="images/logo.png"}}" alt="Innova Store" />',
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();


//Innova Footer Information Links Block
$staticBlock = array(
    'title' => 'Innova Footer Information Links Block',
    'identifier' => 'innova_footer_information_links_block',
    'content' => '<div class="footer-column">
<h4>Shopping Guide</h4>
<ul class="links">
<li class="first"><a title="How to buy" href="#">How to buy</a></li>
<li><a title="FAQs" href="#">FAQs</a></li>
<li><a title="Payment" href="#">Payment</a></li>
<li><a title="Shipment&lt;/a&gt;" href="#">Shipment</a></li>
<li><a title="Where is my order?" href="#">Where is my order?</a></li>
<li class="last"><a title="Return policy" href="#">Return policy</a></li>
</ul>
</div>
<div class="footer-column">
<h4>Style Advisor</h4>
<ul class="links">
<li class="first"><a title="Your Account" href="#">Your Account</a></li>
<li><a title="Information" href="#">Information</a></li>
<li><a title="Addresses" href="#">Addresses</a></li>
<li><a title="Addresses" href="#">Discount</a></li>
<li><a title="Orders History" href="#">Orders History</a></li>
<li class="last"><a title=" Additional Information" href="#">Additional Information</a></li>
</ul>
</div>
<div class="footer-column-last">
<h4>Information</h4>
<ul class="links">
<li class="first"><a title="Site Map" href="{{store_url=catalog/seo_sitemap/category/}}">Site Map</a></li>
<li><a title="Search Terms" href="{{store_url=catalogsearch/term/popular/}}">Search Terms</a></li>
<li><a title="Advanced Search" href="{{store_url=catalogsearch/advanced/}}">Advanced Search</a></li>
<li><a title="Contact Us" href="{{store_url=contacts}}">Contact Us</a></li>
<li><a title="Suppliers" href="#">Suppliers</a></li>
<li class=" last"><a class="link-rss" title="Our stores" href="#">Our stores</a></li>
</ul>
</div>',
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();

//Innova Navigation Product Block
$staticBlock = array(
    'title' => 'Innova Navigation Product Block',
    'identifier' => 'innova_navigation_product_block',
    'content' => '{{block type="catalog/product_new" products_count="1" name="home.catalog.product.new" as="newproduct" template="catalog/product/new.phtml" }}',
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();

//Innova Footer About Us Block
$staticBlock = array(
    'title' => 'Innova Footer About Us Block',
    'identifier' => 'innova_footer_about_us_block',
    'content' => '<div class="footer-column-1">
<h4>About Company</h4>
<p>Our Company is a Magento product development and solutions company. We focus on helping you design, build and manage rich Magento-powered stores.</p>
<address>123 Main Street, Anytown, <br /> CA 12345 USA</address>Phone : +1 800 123 1234<br /> Email : <a href="mailto:support@magikcommerce.com">support@magikcommerce.com</a></div>',
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();


//Innova Home Featured Block
$staticBlock = array(
    'title' => 'Innova Home Featured Block',
    'identifier' => 'innova_home_featured_block',
    'content' => '<div class="offer-banner">
<div class="offer-banner-section">
<div class="col"><img src="{{skin url="images/offer-banner1.jpg"}}" alt="banner" /></div>
<div class="col-mid"><img src="{{skin url="images/offer-banner2.jpg"}}" alt="banner" /></div>
<div class="col"><img src="{{skin url="images/offer-banner3.jpg"}}" alt="banner" /></div>
</div>
</div>',
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();

//Innova Home Offer Banner Block
$staticBlock = array(
    'title' => 'Innova Home Offer Banner Block',
    'identifier' => 'innova_home_offer_banner_block',
    'content' => '<div class="banner-section">
<div class="left-banner"><img src="{{skin url="images/banner1.jpg"}}" alt="banner1" /></div>
<div class="right-banner"><img src="{{skin url="images/banner2.jpg"}}" alt="banner2" /></div>
</div>',
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();

//Innova Contact Us Static Block
$staticBlock = array(
    'title' => 'Innova Contact Us Static Block',
    'identifier' => 'innova_contact_us_static_block',
    'content' => '<div class="block block-company">
<div class="block-title">Company</div>
<div class="block-content"><ol id="recently-viewed-items">
<li class="item odd"><a href="#">About Us</a></li>
<li class="item even"><a href="{{store_url=catalog/seo_sitemap/category/}}">Sitemap</a></li>
<li class="item  odd"><a href="#">Terms of Service</a></li>
<li class="item last"><a href="{{store_url=catalogsearch/term/popular/}}">Search Terms</a></li>
</ol></div>
</div>',
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();

//Innova Call Us Block
$staticBlock = array(
    'title' => 'Innova Call Us Block',
    'identifier' => 'innova_call_us_block',
    'content' => '<div class="phone"><em class="icon-phone">&nbsp;</em>&nbsp;<span>Call Us &nbsp; +1 800 123 1234</span></div>',
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();

//Innova Toplinks Company Block
$staticBlock = array(
    'title' => 'Innova Toplinks Company Block',
    'identifier' => 'innova_toplinks_company_block',
    'content' => '<div class="company">
<div class="click-nav">
<ul class="no-js">
<li><a class="clicker" title="Company">Company <span class="caret">&nbsp;</span></a>
<ul class="link">
<li><a title="About Us" href="{{store_url=about-us}}">About Us</a></li>
<li><a title="Customer Service" href="#">Customer Service</a></li>
<li><a title="Privacy Policy" href="#">Privacy Policy</a></li>
<li><a title="Site Map" href="{{store_url=catalog/seo_sitemap/category/}}"><span>Site Map</span></a></li>
<li><a title="Search Terms" href="{{store_url=catalogsearch/term/popular/}}"><span>Search Terms</span></a></li>
<li class="last"><a title="Advanced Search" href="{{store_url=catalogsearch/advanced/}}"><span>Advanced Search</span></a></li>
</ul>
</li>
</ul>
</div>
</div>',
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();

//Innova Payment Method Block
$staticBlock = array(
    'title' => 'Innova Payment Method Block',
    'identifier' => 'innova_payment_method_block',
    'content' => '<div class="payments"><img src="{{skin url="images/payment.png"}}" alt="payment method" /></div>',
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();

//Innova Footer Freeshipping Banner Block
$staticBlock = array(
    'title' => 'Innova Footer Freeshipping Banner Block',
    'identifier' => 'innova_footer_freeshipping_banner_block',
    'content' => '<div class="prom-section">
<div class="inner">
<div class="col-info">Return &amp; Exchange in <span>3 working days</span></div>
<div class="col-info">Get <span>15%</span> off when you spend over <span>$100</span></div>
<div class="col-info-last">Free Shipping! <span> All Orders Over $299</span></div>
</div>
</div>',
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();

//Innova Home Testimonial Block
$staticBlock = array(
    'title' => 'Innova Home Testimonial Block',
    'identifier' => 'innova_home_testimonial_block',
    'content' => '<div class="testimonials">
<div class="testimonials_RC">
<div class="inner-left">
<div class="quote-box"><span class="quote-left">&nbsp;</span> <q>We had some issues concerning design &amp; layout. The support team was very helpful in adressing these issues. Great help! </q></div>
<span class="quote-arrow">&nbsp;</span> <cite><span class="photo tina"><img src="{{skin url="images/photo-img1.png"}}" alt="" /> </span><span class="author">Jan Doe,</span> CEO, <br />XYZ Softwear </cite></div>
<div class="inner-left">
<div class="quote-box"><span class="quote-left">&nbsp;</span> <q>Great SEO extension. Packs the power of many SEO extension into one easy-to-use extension. Support was great as well. Very satisfied.</q></div>
<span class="quote-arrow">&nbsp;</span> <cite><span class="photo tina"><img src="{{skin url="images/photo-img2.png"}}" alt="" /> </span><span class="author">TheRealKG,</span> Global Marketing Programs, <br />ABC Co </cite></div>
<div class="inner-left" style="margin-right: 0px;">
<div class="quote-box"><span class="quote-left">&nbsp;</span> <q>Excellent product for hiding price, prompt and efficient support. I strongly recommend this product !</q></div>
<span class="quote-arrow">&nbsp;</span> <cite><span class="photo tina"><img src="{{skin url="images/photo-img3.png"}}" alt="" /> </span><span class="author">Farrukh Naveed,</span> Corporate Marketing, <br />XYZ systems </cite></div>
</div>
</div>',
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();

//Innova Left Sidebar Banner Block
$staticBlock = array(
    'title' => 'Innova Left Sidebar Banner Block',
    'identifier' => 'innova_left_sidebar_banner_block',
    'content' => '<div class="block block-banner">
<div id="slides">
<div class="slides_container">
<div><a title="" href="#" target="_self"><img title="" src="{{skin url="images/add-banner.png"}}" alt="" /></a></div>
<div><a title="" href="#" target="_self"><img title="" src="{{skin url="images/add-banner1.png"}}" alt="" /></a></div>
</div>
</div>
<script type="text/javascript">
        jQuery(function(){
        jQuery("#slides").slides({
                generatePagination: false,
                generateNextPrev: false,
                effect: "fade",
                crossfade: true,
                play: 5000
        });
    });
</script>
</div>',
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();

//Innova Blog Banner Ad Block
$staticBlock = array(
    'title' => 'Innova Blog Banner Ad Block',
    'identifier' => 'innova_blog_banner_ad_block',
    'content' => '<div class="ad-spots widget widget__sidebar">
<h3 class="widget-title">Ad Spots</h3>
<div class="widget-content"><a title="" href="#" target="_self"><img src="{{skin url="images/offer-banner1.jpg"}}" alt="offer banner" /></a></div>
</div>',
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();

//Innova Blog Banner Text Block
$staticBlock = array(
    'title' => 'Innova Blog Banner Text Block',
    'identifier' => 'innova_blog_banner_text_block',
    'content' => '<div class="text-widget widget widget__sidebar">
<h3 class="widget-title">Text Widget</h3>
<div class="widget-content">Mauris at blandit erat. Nam vel tortor non quam scelerisque cursus. Praesent nunc vitae magna pellentesque auctor. Quisque id lectus.<br /> <br /> Massa, eget eleifend tellus. Proin nec ante leo ssim nunc sit amet velit malesuada pharetra. Nulla neque sapien, sollicitudin non ornare quis, malesuada.</div>
</div>',
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();

//Innova Home Latest Blog Block
$staticBlock = array(
    'title' => 'Innova Home Latest Blog Block',
    'identifier' => 'innova_home_latest_blog_block',
    'content' => '<p>{{block type="blogmate/index" name="blog_home" template="blogmate/right/home_right.phtml"}}</p>',
    'is_active' => 1,
    'stores' => array(0)
);
Mage::getModel('cms/block')->setData($staticBlock)->save();

}
catch (Exception $e) {
    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('An error occurred while installing Innova theme pages and cms blocks.'));
}
