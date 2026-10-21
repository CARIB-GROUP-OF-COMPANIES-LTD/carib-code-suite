import urllib.request
import http.cookiejar
import re

cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

# 1. Authenticate with quick_unlock & user=admin
sso_url = "https://test.carib.core.caribgroupofcompanies.ltd/sso.php?sso_token=quick_unlock&user=admin"
r1 = opener.open(urllib.request.Request(sso_url, headers={"User-Agent": "Mozilla/5.0"}))

# 2. Fetch extensions page
ext_url = "https://test.carib.core.caribgroupofcompanies.ltd/index.php?module=ITS4YouInstaller&parent=Settings&view=Extensions"
r2 = opener.open(urllib.request.Request(ext_url, headers={"User-Agent": "Mozilla/5.0"}))
html = r2.read().decode("utf-8", errors="ignore")

# Find all buttons with installExtension
more_details = re.findall(r"<button[^>]*class=[\"\'][^\"\']*installExtension[^\"\']*[\"\'][^>]*>", html)
print(f"Total More Details Buttons Found: {len(more_details)}")

print("\n--- Sample Button Targets ---")
for b in more_details[:15]:
    u = re.search(r"data-url=\"([^\"]+)\"", b)
    if u:
        print("  ->", u.group(1))

external_vendor = [b for b in more_details if "it-solutions4you.com" in b]
print(f"\nExternal vendor links in buttons: {len(external_vendor)}")
carib_links = [b for b in more_details if "solution_details.php" in b]
print(f"Carib solution_details.php links in buttons: {len(carib_links)}")
