
import json
from playwright.sync_api import sync_playwright

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()

    # Mock Auth
    auth_data = json.dumps({"token": "test-token", "username": "admin", "profile_image": "default.png"})

    # Define route handler
    def handle_route(route):
        url = route.request.url
        if "action=getProducts" in url:
            route.fulfill(
                status=200,
                content_type="application/json",
                body=json.dumps({
                    "success": True,
                    "data": [
                        {"id": 1, "name": "Test Product", "image": "test.png", "prodActive": "1", "models": [{"sell": "1000"}]}
                    ]
                })
            )
        elif "action=updateProductStatus" in url:
            # Check body
            post_data = route.request.post_data
            print(f"Update Status Request: {post_data}")
            route.fulfill(
                status=200,
                content_type="application/json",
                body=json.dumps({"success": True, "message": "Updated"})
            )
        elif "action=getOrders" in url:
            route.fulfill(
                status=200,
                content_type="application/json",
                body=json.dumps({"success": True, "data": []})
            )
        elif "action=getDelivery" in url: # For Wilayas
            route.fulfill(
                status=200,
                content_type="application/json",
                body=json.dumps({"success": True, "data": [{"wilaya_id": 1, "wilaya_name": "Adrar"}]}) # Mock response structure might need adjustment
            )
        else:
            # Default empty success
            route.fulfill(
                status=200,
                content_type="application/json",
                body=json.dumps({"success": True, "data": []})
            )

    page = context.new_page()

    # Add auth to local storage before navigation
    page.add_init_script(f"""
        localStorage.setItem('auth', '{auth_data}');
    """)

    # Intercept API calls
    page.route("**/backend/api.php?action=*", handle_route)
    page.route("**/backend/api.php", handle_route) # For POSTs often action is in query or body, but handle_route logic checks url string

    print("Navigating to Products...")
    try:
        page.goto("http://localhost:3000/products", timeout=60000)
        page.wait_for_load_state("networkidle")
    except Exception as e:
        print(f"Navigation failed: {e}")
        # Capture what we have
        page.screenshot(path="/home/jules/verification/failed_nav.png")
        browser.close()
        return

    # Verify Product Status Toggle
    print("Verifying Product Status Toggle...")
    # Look for "active" text or the indicator
    try:
        active_indicator = page.locator(".activer_info_float").first
        if active_indicator.is_visible():
            print("Found active indicator.")
            # Click it
            active_indicator.click()
            print("Clicked active indicator.")
            # We expect the request to be logged by handle_route print
            page.wait_for_timeout(1000) # Wait for reaction
            # Take screenshot of products page
            page.screenshot(path="/home/jules/verification/products_page.png")
        else:
            print("Active indicator not found.")
    except Exception as e:
        print(f"Product verification failed: {e}")

    # Verify Orders Filter
    print("Navigating to Orders...")
    try:
        page.goto("http://localhost:3000/orders", timeout=60000)
        page.wait_for_load_state("networkidle")

        # Click filter button (RectBtn with svg filter)
        # Using selector based on SVG path or class if possible.
        # The code has <RectBtn ... svg="filter" ... />
        # RectBtn usually renders a button.

        # Try to find button with filter icon or just the filter button in the boxRow
        # The code: <RectBtn style="width: 10%;" svg="filter" @click:ok="isFilter = true" />

        # Let's try to click the button that opens the filter
        # It might be the second button in boxRow?
        page.locator(".boxRow button").last.click() # Assuming Search is first, Filter btn is second
        print("Clicked filter button.")

        page.wait_for_timeout(1000)

        # Check if filter modal appeared
        filter_wrapper = page.locator(".filter-wrapper")
        if filter_wrapper.is_visible():
            print("Filter modal visible.")
            # Check for Selector component (floating-input3)
            # We updated filter.vue to use Selector for "Product", "Wilaya", etc.
            selectors = page.locator(".floating-input3")
            count = selectors.count()
            print(f"Found {count} Selectors.")

            inputs = page.locator(".floating-input2") # Inputer uses floating-input2 ? Check input.vue
            # Inputer.vue uses class="floating-input2"
            input_count = inputs.count()
            print(f"Found {input_count} Inputers.")

            page.screenshot(path="/home/jules/verification/orders_filter.png")
        else:
            print("Filter modal not visible.")
            page.screenshot(path="/home/jules/verification/orders_no_filter.png")

    except Exception as e:
        print(f"Orders verification failed: {e}")

    browser.close()

with sync_playwright() as playwright:
    run(playwright)
