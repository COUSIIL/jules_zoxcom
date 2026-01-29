from playwright.sync_api import sync_playwright
import json
import time

def run(page):
    # Mock API
    def handle_route(route):
        url = route.request.url
        if "action=giminiImageGen" in url:
            route.fulfill(json={"success": True, "taskId": "test-task"})
        elif "action=giminiPoll" in url:
            route.fulfill(json={"success": True, "state": "success", "resultUrls": ["https://placehold.co/600x400"]})
        elif "action=saveExternalUrl" in url:
            route.fulfill(json={"success": True, "data": {"path": "https://placehold.co/600x400"}})
        elif "action=checkConnexion" in url:
             route.fulfill(json={"success": True, "user": {"id": 1, "username": "admin"}})
        elif "action=" in url:
            route.continue_()
        else:
            route.continue_()

    page.route("**/backend/api.php*", handle_route)

    # Set auth
    page.goto("http://localhost:3000/connexion")
    page.evaluate("localStorage.setItem('auth', JSON.stringify({token: 'mock-token'}))")

    # Navigate
    print("Navigating to /varqAI/image...")
    page.goto("http://localhost:3000/varqAI/image")

    # Fill prompt
    print("Filling prompt...")
    input_loc = page.locator(".floating-input2 input").first
    input_loc.click()
    page.keyboard.type("A beautiful sunset")

    page.wait_for_timeout(500)

    # Check value
    print(f"Input value: {input_loc.input_value()}")

    # Click Generate
    print("Clicking generate...")
    # Force click even if disabled check fails (but it won't trigger action if disabled)
    page.get_by_role("button", name="Générer l'image").click(timeout=5000)

    # Wait for image
    print("Waiting for image...")
    page.wait_for_selector(".result-card img", timeout=10000)

    # Screenshot
    print("Taking screenshot...")
    page.screenshot(path="/home/jules/verification/verification.png")

if __name__ == "__main__":
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()
        try:
            run(page)
            print("Verification script finished successfully.")
        except Exception as e:
            print(f"Verification script failed: {e}")
            page.screenshot(path="/home/jules/verification/verification_failed.png")
        finally:
            browser.close()
