document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.querySelector('.quantity-input');
    const minusBtn = document.querySelector('.quantity-btn.minus');
    const plusBtn = document.querySelector('.quantity-btn.plus');

    minusBtn.addEventListener('click', () => {
        let value = parseInt(quantityInput.value);
        if (value > 1) {
            quantityInput.value = value - 1;
        }
    });

    plusBtn.addEventListener('click', () => {
        let value = parseInt(quantityInput.value);
        quantityInput.value = value + 1;
    });

    quantityInput.addEventListener('change', () => {
        let value = parseInt(quantityInput.value);
        if (value < 1 || isNaN(value)) {
            quantityInput.value = 1;
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const shoppingCart = document.querySelector('.shopping-cart');

    if (shoppingCart) {
        shoppingCart.addEventListener('click', function(event) {
            const target = event.target;

            // Xử lý nút tăng số lượng
            if (target.closest('.qty-control__increase')) {
                const qtyControl = target.closest('.qty-control');
                const input = qtyControl.querySelector('.qty-control__number');
                const currentQty = parseInt(input.value) || 0;
                updateQuantity(input, currentQty);
            }

            // Xử lý nút giảm số lượng
            if (target.closest('.qty-control__reduce')) {
                const qtyControl = target.closest('.qty-control');
                const input = qtyControl.querySelector('.qty-control__number');
                const currentQty = parseInt(input.value) || 1;
                if (currentQty >= 1) {
                    updateQuantity(input, currentQty);
                }
            }
        });

        shoppingCart.addEventListener('change', function(event) {
            if (event.target.matches('.qty-control__number')) {
                const input = event.target;
                let newQty = parseInt(input.value) || 1;
                // Đảm bảo số lượng tối thiểu là 1
                newQty = Math.max(1, newQty);
                updateQuantity(input, newQty);
            }
        });
    }
});

function updateQuantity(input, newQuantity) {
    const productVariantId = input.getAttribute('data-id');
    const loadingOverlay = createLoadingOverlay(input);

    // Hiển thị loading
    loadingOverlay.style.display = 'flex';

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: '/cart/update-cart-quantity', // Đường dẫn tới route xử lý
        method: 'POST',
        data: {
            product_variant_id: productVariantId,
            quantity: newQuantity
        },
        success: function(response) {
            if (response.success) {

                input.value = response.quantity;

                // Cập nhật tổng tiền của sản phẩm
                const row = input.closest('tr');
                const subtotalElement = row.querySelector('.shopping-cart__subtotal');
                if (subtotalElement) {
                    subtotalElement.textContent = response.total + ' VND';
                }

                input.style.backgroundColor = '#e8f5e9';
                setTimeout(() => {
                    input.style.backgroundColor = '';
                }, 300);
            } else {
                alert(response.message || 'An error occurred while updating the quantity.');
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr);
            Swal.fire({
                icon: 'error',
                title: 'error',
                text: 'The quantity of products in stock is insufficient.!',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload();
                }
            });
        },

        complete: function() {
            loadingOverlay.style.display = 'none';
        }
    });
}

// Tạo loading overlay
function createLoadingOverlay(input) {
    let overlay = input.closest('.qty-control').querySelector('.loading-overlay');

    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10;
        `;

        const spinner = document.createElement('div');
        spinner.className = 'spinner';
        spinner.style.cssText = `
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        `;

        overlay.appendChild(spinner);
        input.closest('.qty-control').appendChild(overlay);

        // Thêm keyframe animation
        if (!document.querySelector('#spinnerAnimation')) {
            const style = document.createElement('style');
            style.id = 'spinnerAnimation';
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
    }

    return overlay;
}

$(document).ready(function() {
    $('.product-select-checkbox').on('change', function() {
        let totalAmount = 0;

        $('.product-select-checkbox:checked').each(function() {
            let productId = $(this).data('id');
            let quantity = parseInt($(this).closest('tr').find('.qty-control__number').val());
            let price = parseFloat($(this).closest('tr').find('.shopping-cart__product-price').text().replace(' VND', '').replace(',', ''));

            totalAmount += price * quantity;
        });

        $('#totalAmount').text(totalAmount.toLocaleString('vi-VN') + ',000 VND');

    });
});





function changeImage(src) {
    // Change main image
    document.getElementById('mainImage').src = src;
    // Update zoom link
    document.querySelector('.zoom-btn').href = src;

    // Update active state of thumbnails
    const thumbs = document.querySelectorAll('.thumb-item');
    thumbs.forEach(thumb => {
        if(thumb.querySelector('img').src === src) {
            thumb.classList.add('active');
        } else {
            thumb.classList.remove('active');
        }
    });
}

// Initialize Fancybox
document.addEventListener('DOMContentLoaded', function() {
    Fancybox.bind('[data-fancybox="gallery"]', {
        loop: true
    });
});

function changeImage(src) {
    document.getElementById('mainImage').src = src;
}

document.addEventListener('DOMContentLoaded', function () {
    const swiper = new Swiper('.swiper-container', {
        // Enable navigation buttons
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        loop: true, // Enables infinite scrolling
        slidesPerView: 1,
        spaceBetween: 10,
    });
});

$(document).on('click', '.cart-table .remove-cart-v2', function(e) {
    e.preventDefault();
    let deleteId = $(this).data('id');

    Swal.fire({
        title: 'Are you sure you want to delete this product?',
        text: 'This action cannot be reversed!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, keep it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '/cart/delete/',
                method: 'POST',
                data: { deleteId: deleteId },
                success: function(res) {
                    Swal.fire(
                        'Deleted!',
                        'Product has been successfully removed from cart!',
                        'success'
                    );
                    $('.table-data').html(res);
                },
                error: function(res) {
                    if (res.status === 404) {
                        $('.table-data').html('<p class="alert alert-primary">Không tìm thấy kết quả!</p>');
                    } else {
                        $('.table-data').html('<p class="alert alert-danger">Có lỗi xảy ra! Vui lòng thử lại.</p>');
                    }
                }
            });

            let parentEl = $(this).closest('tr');
            $(parentEl).addClass('_removed');
            setTimeout(() => {
                $(parentEl).remove();
            }, 350);
        }
    });
});

