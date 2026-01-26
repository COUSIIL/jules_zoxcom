from playwright.sync_api import sync_playwright
import time
import os

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context(viewport={'width': 375, 'height': 812}) # Mobile size (iPhone X)
    page = context.new_page()

    # Route APIs
    def handle_api(route):
        url = route.request.url
        print(f"Intercepted: {url}")
        if "checkConnexion" in url:
            route.fulfill(json={
                "success": True,
                "data": {
                    "id": 1, "username": "test", "settings": {"notifications": True},
                    "role": "admin",
                    "role_id": 1,
                    "profile_image": None,
                    "created_at": "2023-01-01",
                    "permissions": []
                }
            })
        elif "getStatistics" in url:
            route.fulfill(json={
                "success": True,
                "dashboard": {
                   "today": {"total": 10, "completed": 5, "canceled": 1, "confirmed": 2, "other": 2},
                   "week": {"total": 50, "completed": 20, "canceled": 5, "confirmed": 10, "other": 15},
                   "month": {"total": 200, "completed": 100, "canceled": 20, "confirmed": 50, "other": 30}
                },
                "analysis": {
                    "trend": [{"date_label": "Mon", "count": 10}, {"date_label": "Tue", "count": 15}],
                    "topWilayas": [{"delivery_zone": "Algiers", "count": 50}],
                    "topProducts": [{"product_name": "Product A", "count": 20}]
                }
            })
        elif "getUsers" in url:
             route.fulfill(json={"success": True, "data": [{"id": 1, "username": "test"}]})
        elif "getPinnedOrders" in url:
             route.fulfill(json={"success": True, "data": []})
        elif "notificationApi" in url:
             route.fulfill(json={
                 "success": True,
                 "notifications": [
                     {"id": 1, "title": "Welcome", "body": "Welcome to the app!", "type": "info", "is_read": 0, "created_at": "2023-10-27T10:00:00"}
                 ],
                 "unread_count": 1
             })
        else:
            route.continue_()

    context.route("**/*", handle_api)

    # Set local storage
    page.add_init_script("""
        localStorage.setItem('auth', JSON.stringify({"token":"dummy","id":1,"role":"admin"}));
    """)

    try:
        # 1. Dashboard (Mobile)
        print("Navigating to Dashboard...")
        page.goto("http://localhost:3000/", timeout=60000)
        page.wait_for_timeout(5000) # Wait for charts and initial loads
        page.screenshot(path="/home/jules/verification/dashboard_mobile.png", full_page=True)

        # 2. Notifications
        print("Navigating to Notifications...")
        page.goto("http://localhost:3000/notifications", timeout=60000)
        page.wait_for_timeout(3000)
        page.screenshot(path="/home/jules/verification/notifications.png", full_page=True)

        # 3. Settings
        print("Navigating to Settings...")
        page.goto("http://localhost:3000/setings", timeout=60000)
        page.wait_for_timeout(3000)
        page.screenshot(path="/home/jules/verification/settings.png", full_page=True)
    except Exception as e:
        print(f"Error: {e}")
    finally:
        browser.close()

with sync_playwright() as p:
    run(p)
