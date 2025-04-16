<?php 
// ecommerce.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$host = "localhost";
$username = "root";
$password = "";
$dbname = "kasir_reddra";
$mysqli = new mysqli($host, $username, $password, $dbname);
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Query data produk dengan kategori (produk.* mencakup deskripsi)
$sql_products = "SELECT produk.*, kategori.nama_kategori 
                FROM produk 
                LEFT JOIN kategori ON produk.kategori_id = kategori.kategori_id";
$result_products = $mysqli->query($sql_products);

// Ambil data kategori (jika diperlukan)
$sql_categories = "SELECT * FROM kategori";
$categories = $mysqli->query($sql_categories)->fetch_all(MYSQLI_ASSOC);

// Ambil data pelanggan
$sql_pelanggan = "SELECT * FROM pelanggan";
$result_pelanggan = $mysqli->query($sql_pelanggan);

// Total produk
$total_produk = $mysqli->query("SELECT COUNT(*) AS total FROM produk")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Toko Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    :root {
        --primary: #FF6B6B;
        --secondary: #4ECDC4;
        --dark: #2D3436;
        --light: #F9F9F9;
        --danger: #e74c3c;
    }

    body {
        background: var(--light);
        font-family: 'Poppins', sans-serif;
        margin: 0;
    }

    /* Navbar */
    .nav-container {
        background: white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 1rem;
        position: fixed;
        width: 100%;
        top: 0;
        z-index: 100;
    }

    .nav-content {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
    }

    .total-products {
        background: var(--secondary);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
    }

    .search-filter {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .search-box {
        position: relative;
    }

    .search-input {
        padding: 0.5rem 2rem 0.5rem 1rem;
        border: 2px solid #ddd;
        border-radius: 20px;
        width: 250px;
    }

    .search-icon {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
    }

    .filter-group {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        background: var(--light);
        border: 1px solid #ddd;
        cursor: pointer;
        transition: all 0.3s;
        white-space: nowrap;
    }

    .filter-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    /* Product Grid */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 2rem;
        padding: 160px 20px 20px;
    }

    .product-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s;
        position: relative;
    }

    .product-card:hover {
        transform: translateY(-5px);
    }

    .product-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: var(--secondary);
        color: white;
        padding: 0.3rem 0.8rem;
        border-radius: 10px;
        font-size: 0.9rem;
    }

    .product-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-bottom: 2px solid #eee;
    }

    .product-info {
        padding: 1.5rem;
    }

    .product-price {
        color: var(--primary);
        font-weight: 700;
        font-size: 1.4rem;
        margin: 0.5rem 0;
    }

    .product-stock {
        color: #666;
        font-size: 0.9rem;
    }

    .product-description {
        margin-top: 0.5rem;
        font-size: 0.85rem;
        color: #333;
    }

    .btn-buy {
        width: 100%;
        padding: 0.8rem;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    .btn-edit,
    .btn-delete {

        padding: 0.8rem;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
        text-decoration: none;
    }

    .btn-buy {
        background: var(--primary);
        color: white;
    }

    .btn-buy:hover {
        background: #FF5252;
    }

    .btn-edit {
        background: var(--secondary);
        color: white;
        text-decoration: none;
    }

    .btn-edit:hover {
        background: #3bb3a3;
    }

    .btn-delete {
        background: var(--danger);
        color: white;
    }

    .btn-delete:hover {
        background: #c0392b;
    }

    /* Admin Links */
    .admin-links {
        position: fixed;
        bottom: 20px;
        left: 20px;
        display: none;
        gap: 1rem;
        z-index: 100;
        background: white;
        padding: 1rem;
        border-radius: 30px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .admin-btn {
        padding: 0.8rem 1.5rem;
        background: var(--secondary);
        color: white;
        border-radius: 30px;
        text-decoration: none;
        transition: transform 0.3s;
    }

    .admin-btn:hover {
        transform: translateY(-2px);
    }

    .toggle-admin {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
        cursor: pointer;
        z-index: 101;
    }

    /* Order Modal */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        backdrop-filter: blur(5px);
    }

    .modal-content {
        background: white;
        padding: 2rem;
        border-radius: 20px;
        width: 90%;
        max-width: 400px;
        max-height: 80vh;
        overflow-y: auto;
        position: relative;
        animation: modalEnter 0.3s ease;
    }

    @keyframes modalEnter {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--dark);
    }

    .modal-input-group {
        margin: 1rem 0;
    }

    .modal-label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--dark);
    }

    .modal-input {
        width: 100%;
        padding: 0.8rem;
        border: 2px solid #ddd;
        border-radius: 10px;
        font-size: 1rem;
    }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div class="nav-container">
        <div class="nav-content">
            <div>
                <h1>Produk</h1>
                <div class="total-products">Total Produk: <?= $total_produk ?></div>
            </div>
            <div class="search-filter">
                <div class="search-box">
                    <input type="text" class="search-input" placeholder="Cari produk..." id="searchInput">
                    <i class="fas fa-search search-icon"></i>
                </div>
                <div class="filter-group">
                    <button class="filter-btn" onclick="sortProducts('price_high')">Harga Tertinggi</button>
                    <button class="filter-btn" onclick="sortProducts('price_low')">Harga Terendah</button>
                    <button class="filter-btn" onclick="sortProducts('stock_high')">Stok Terbanyak</button>
                    <button class="filter-btn" onclick="sortProducts('stock_low')">Stok Tersedikit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Button Admin -->
    <button class="toggle-admin" onclick="toggleAdminLinks()"><i class="fas fa-bars"></i></button>
    <!-- Admin Links -->
    <div class="admin-links" id="adminLinks">
        <a href="tambah_produk.php" class="admin-btn"><i class="fas fa-plus"></i> Tambah Produk</a>
        <a href="kategori/kategori.php" class="admin-btn"><i class="fas fa-tag"></i> Kelola Kategori</a>
    </div>

    <!-- Product Grid -->
    <div class="container">
        <div class="product-grid" id="productGrid">
            <?php while($product = $result_products->fetch_assoc()): ?>
            <div class="product-card" data-category="<?= $product['kategori_id'] ?>"
                data-price="<?= $product['Harga'] ?>" data-stock="<?= $product['Stok'] ?>"
                data-name="<?= strtolower($product['NamaProduk']) ?>" data-product="<?= $product['ProdukID'] ?>">
                <?php if($product['Stok'] <= 0): ?>
                <div class="product-badge">Habis</div>
                <?php endif; ?>
                <img src="<?= $product['gambar'] ?>" class="product-image" alt="<?= $product['NamaProduk'] ?>">
                <div class="product-info">
                    <h3><?= $product['NamaProduk'] ?></h3>
                    <p class="product-price">Rp<?= number_format($product['Harga'],0,',','.') ?></p>
                    <p class="product-stock">Stok: <?= $product['Stok'] ?></p>
                    <!-- Tampilkan view deskripsi jika tersedia -->
                    <?php if(!empty($product['deskripsi'])): ?>
                    <p class="product-description"><?= $product['deskripsi'] ?></p>
                    <?php endif; ?>
                    <?php if($product['Stok'] > 0): ?>
                    <button class="btn-buy" onclick="showOrderModal(
                                <?= $product['ProdukID'] ?>,
                                '<?= addslashes($product['NamaProduk']) ?>',
                                <?= $product['Harga'] ?>,
                                <?= $product['Stok'] ?>,
                                '<?= $product['gambar'] ?>'
                            )"><i class="fas fa-cart-plus"></i> Beli Sekarang</button>
                    <?php else: ?>
                    <button class="btn-buy" disabled><i class="fas fa-times"></i> Stok Habis</button>
                    <?php endif; ?>
                    <!-- Tombol Edit dan Hapus -->
                    <a href="edit_produk.php?id=<?= $product['ProdukID'] ?>" class="btn-edit"><i
                            class="fas fa-edit"></i> Edit</a>
                    <a href="hapus_produk.php?id=<?= $product['ProdukID'] ?>" class="btn-delete"
                        onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');"><i
                            class="fas fa-trash"></i> Hapus</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Order Modal -->
    <div class="modal-overlay" id="orderModal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle"></h2>
            <img id="modalImage" src="" alt="" style="width:100%; height:200px; object-fit:cover; border-radius:10px;">
            <div style="margin:1rem 0;">
                <p>Harga: <span id="modalPrice"></span></p>
                <p>Stok Tersedia: <span id="modalStock"></span></p>
            </div>
            <form id="orderForm" onsubmit="processOrder(event)">
                <div class="modal-input-group">
                    <label class="modal-label" for="pelanggan_id">Pelanggan</label>
                    <select id="pelanggan_id" class="modal-input" required>
                        <option value="">-- Pilih Pelanggan --</option>
                        <?php 
                        // Reset pointer jika perlu
                        $result_pelanggan->data_seek(0);
                        while($pelanggan = $result_pelanggan->fetch_assoc()): ?>
                        <option value="<?= $pelanggan['PelangganID'] ?>">
                            <?= $pelanggan['NamaPelanggan'] ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="modal-input-group">
                    <label class="modal-label" for="quantity">Jumlah</label>
                    <input type="number" id="quantity" class="modal-input" min="1" value="1" required
                        oninput="calculateModalTotal()">
                </div>
                <div class="modal-input-group">
                    <label class="modal-label" for="discount">Diskon (%)</label>
                    <input type="number" id="discount" class="modal-input" min="0" step="0.01" value="0" required
                        oninput="calculateModalTotal()">
                </div>
                <div class="modal-input-group">
                    <label class="modal-label" for="tax">Pajak (%)</label>
                    <input type="number" id="tax" class="modal-input" min="0" step="0.01" value="0" required
                        oninput="calculateModalTotal()">
                </div>
                <div class="modal-input-group">
                    <label class="modal-label" for="final_total">Total</label>
                    <input type="text" id="final_total" class="modal-input" readonly value="0">
                </div>
                <button type="submit" class="btn-buy"><i class="fas fa-check"></i> Konfirmasi Pembelian</button>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    let currentProduct = null;
    let products = Array.from(document.querySelectorAll('.product-card'));

    // Fungsi pencarian produk
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        products.forEach(product => {
            const productName = product.dataset.name;
            const category = product.querySelector('h3').textContent.toLowerCase();
            product.style.display = (productName.includes(term) || category.includes(term)) ? 'block' :
                'none';
        });
    });

    // Fungsi sorting produk
    function sortProducts(type) {
        const sortedProducts = [...products].sort((a, b) => {
            const key = type.split('_')[0];
            const aVal = parseFloat(a.dataset[key]);
            const bVal = parseFloat(b.dataset[key]);
            return type.includes('high') ? bVal - aVal : aVal - bVal;
        });
        const productGrid = document.getElementById('productGrid');
        productGrid.innerHTML = '';
        sortedProducts.forEach(product => productGrid.appendChild(product));
    }

    // Toggle Admin Links
    function toggleAdminLinks() {
        const adminLinks = document.getElementById('adminLinks');
        adminLinks.style.display = (adminLinks.style.display === 'flex') ? 'none' : 'flex';
    }

    // Modal Order
    function showOrderModal(id, name, price, stock, image) {
        currentProduct = {
            id,
            name,
            price,
            stock,
            image
        };
        document.getElementById('modalTitle').textContent = name;
        document.getElementById('modalPrice').textContent = 'Rp' + price.toLocaleString('id-ID');
        document.getElementById('modalStock').textContent = stock;
        document.getElementById('modalImage').src = image;
        document.getElementById('quantity').max = stock;
        document.getElementById('quantity').value = 1;
        document.getElementById('discount').value = 0;
        document.getElementById('tax').value = 0;
        calculateModalTotal();
        document.getElementById('orderModal').style.display = 'flex';
    }

    // Hitung total di modal
    function calculateModalTotal() {
        if (!currentProduct) return;
        const quantity = parseInt(document.getElementById('quantity').value) || 0;
        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const tax = parseFloat(document.getElementById('tax').value) || 0;
        const price = currentProduct.price;
        let subtotal = price * quantity;
        let discountAmount = (discount / 100) * subtotal;
        let subAfterDiscount = subtotal - discountAmount;
        let taxAmount = (tax / 100) * subAfterDiscount;
        let total = subAfterDiscount + taxAmount;
        document.getElementById('final_total').value = total.toFixed(2);
    }

    async function processOrder(e) {
        e.preventDefault();
        const formData = new FormData();
        formData.append('product_id', currentProduct.id);
        formData.append('quantity', document.getElementById('quantity').value);
        formData.append('pelanggan_id', document.getElementById('pelanggan_id').value);
        formData.append('user_id', <?= $_SESSION['user_id'] ?>);
        formData.append('discount', document.getElementById('discount').value);
        formData.append('tax', document.getElementById('tax').value);

        try {
            const response = await fetch('process_order.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Pesanan berhasil diproses',
                timer: 2000
            });
            closeModal();
            // Update stok produk di tampilan
            const productCards = document.querySelectorAll('.product-card');
            productCards.forEach(card => {
                if (card.dataset.product == currentProduct.id) {
                    card.querySelector('.product-stock').textContent = 'Stok: ' + result.new_stock;
                }
            });
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Pesanan berhasil diproses',
                timer: 2000
            });
            closeModal();
        }
    }

    function closeModal() {
        document.getElementById('orderModal').style.display = 'none';
        currentProduct = null;
        document.getElementById('pelanggan_id').value = '';
    }
    </script>
</body>

</html>
<?php $mysqli->close(); ?>