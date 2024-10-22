<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="dashboard" class="app-brand-link">
            <img src="<?= APP_LOGO ?>" srcset="<?= APP_LOGO ?> 2x,<?= APP_LOGO ?> 3x" class="img-fluid"
                alt="<?= APP_NAME ?>" height="auto" />
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <li class="menu-item">
            <a href="dashboard" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Fluid">Dashboard</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-package"></i>
                <div data-i18n="Account Settings">Products</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="product" class="menu-link">
                        <div data-i18n="SupplierAdd">Add</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="product-list" class="menu-link">
                        <div data-i18n="SupplierList">List</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="product-category" class="menu-link">
                        <div data-i18n="SupplierList">Product Category</div>
                    </a>
                </li>
            </ul>
        </li>
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-store"></i>
                <div data-i18n="Account Settings">Supplier</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="supplier" class="menu-link">
                        <div data-i18n="SupplierAdd">Add</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="supplier-list" class="menu-link">
                        <div data-i18n="SupplierList">List</div>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</aside>