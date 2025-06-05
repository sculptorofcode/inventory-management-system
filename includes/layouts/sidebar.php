<div id="loader" class="loader-overlay">
    <div class="spinner"></div>
</div>
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
            <a href="inventory-analytics" class="menu-link">
                <i class="menu-icon tf-icons bx bx-chart"></i>
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
                        <div>Add</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="product-list" class="menu-link">
                        <div>List</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="product-category" class="menu-link">
                        <div>Product Category</div>
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
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-archive"></i>
                <div>Stock Management</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="stock-list" class="menu-link">
                        <div>Stock List</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="location-history" class="menu-link">
                        <div>Location History</div>
                    </a>
                </li>
            </ul>
        </li>
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class='menu-icon bx bxs-store-alt'></i>
                <div data-i18n="Account Settings">Warehouse</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="warehouse" class="menu-link">
                        <div data-i18n="WarehouseAdd">Warehouses</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="warehouse-location" class="menu-link">
                        <div data-i18n="WarehouseList">Locations</div>
                    </a>
                </li>
            </ul>
        </li>
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cart"></i>
                <div data-i18n="Account Settings">Purchase Order</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="purchase" class="menu-link">
                        <div>Add</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="purchase-list" class="menu-link">
                        <div>List</div>
                    </a>
                </li>
            </ul>
        </li>
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cart"></i>
                <div data-i18n="Account Settings">Sale Order</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="sale" class="menu-link">
                        <div>Add</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="sale-list" class="menu-link">
                        <div>List</div>
                    </a>
                </li>
            </ul>
        </li>
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div data-i18n="Account Settings">Customer</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="customer" class="menu-link">
                        <div data-i18n="CustomerAdd">Add</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="customer-list" class="menu-link">
                        <div data-i18n="CustomerList">List</div>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</aside>