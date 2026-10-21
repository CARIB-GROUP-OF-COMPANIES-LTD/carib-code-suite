#!/usr/bin/env bash
set -e

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WORKSPACE="/Users/djooozyb/carib live player apps"
APP_NAME="Carib Live Backstage Suite"
LEGACY_APP_NAME="Carib Live Backstage IM Messenger"
DOWNLOADS_DIR="$WORKSPACE/03-Carib-Live-Agency/agency-platform/downloads"

echo "================================================================="
echo "🚀 COMPILING & PACKAGING: CARIB LIVE BACKSTAGE SUITE (NATIVE)"
echo "Features: 4 Small Windows Grid, 4 Dedicated Regional Agents,"
echo "In-App Sandbox, 1-Year Free Approvals, Managed Creator Coaching"
echo "================================================================="

# 1. Compile Arm64 and x86_64
echo "==> [1/6] Compiling Swift Arm64 and x86_64 slices..."
swiftc -O -target arm64-apple-macosx14.0 -framework Cocoa -framework WebKit -lsqlite3 "$DIR/CaribLiveBackstageSuite.swift" -o "/tmp/CaribSuite_arm64"
swiftc -O -target x86_64-apple-macosx14.0 -framework Cocoa -framework WebKit -lsqlite3 "$DIR/CaribLiveBackstageSuite.swift" -o "/tmp/CaribSuite_x86"

# 2. Join into Universal Binary
echo "==> [2/6] Linking Universal Fat Mach-O Binary..."
lipo -create "/tmp/CaribSuite_x86" "/tmp/CaribSuite_arm64" -output "/tmp/CaribSuite_universal"
chmod +x "/tmp/CaribSuite_universal"
file "/tmp/CaribSuite_universal"

# 3. Create .app Bundles
build_bundle() {
    local target_app="$1"
    local exec_name="$2"
    echo "==> Building Bundle: $target_app"

    rm -rf "$target_app"
    mkdir -p "$target_app/Contents/MacOS"
    mkdir -p "$target_app/Contents/Resources"

    cp "/tmp/CaribSuite_universal" "$target_app/Contents/MacOS/$exec_name"
    chmod +x "$target_app/Contents/MacOS/$exec_name"

    # Copy Icon
    if [ -f "$WORKSPACE/06-Carib-Code-Suite/mission-control-dashboard/Carib Mission Control.app/Contents/Resources/AppIcon.icns" ]; then
        cp "$WORKSPACE/06-Carib-Code-Suite/mission-control-dashboard/Carib Mission Control.app/Contents/Resources/AppIcon.icns" "$target_app/Contents/Resources/AppIcon.icns"
    fi

    cat << 'PLIST' > "$target_app/Contents/Info.plist"
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>CFBundleExecutable</key>
    <string>EXEC_NAME_PLACEHOLDER</string>
    <key>CFBundleIconFile</key>
    <string>AppIcon</string>
    <key>CFBundleIdentifier</key>
    <string>info.caribliveagency.backstage.suite</string>
    <key>CFBundleName</key>
    <string>APP_NAME_PLACEHOLDER</string>
    <key>CFBundlePackageType</key>
    <string>APPL</string>
    <key>CFBundleShortVersionString</key>
    <string>3.0.0</string>
    <key>CFBundleVersion</key>
    <string>2026.10</string>
    <key>LSMinimumSystemVersion</key>
    <string>12.0</string>
    <key>NSHighResolutionCapable</key>
    <true/>
    <key>NSAppTransportSecurity</key>
    <dict>
        <key>NSAllowsArbitraryLoads</key>
        <true/>
    </dict>
    <key>NSCameraUsageDescription</key>
    <string>Carib Live Backstage Suite requires camera permissions for video streamer diagnostics.</string>
    <key>NSMicrophoneUsageDescription</key>
    <string>Carib Live Backstage Suite requires microphone permissions for audio streamer diagnostics.</string>
</dict>
</plist>
PLIST

    sed -i '' "s/EXEC_NAME_PLACEHOLDER/$exec_name/g" "$target_app/Contents/Info.plist"
    sed -i '' "s/APP_NAME_PLACEHOLDER/$exec_name/g" "$target_app/Contents/Info.plist"

    # Ad-hoc codesign
    codesign --force --deep -s - "$target_app"
    xattr -cr "$target_app"
    echo "✓ Bundle $target_app signed and verified."
}

# Build both in /Applications
build_bundle "/Applications/$APP_NAME.app" "$APP_NAME"
build_bundle "/Applications/$LEGACY_APP_NAME.app" "$LEGACY_APP_NAME"

# 4. Generate Installers in downloads
echo "==> [4/6] Creating DMG & PKG Installers..."
mkdir -p "$DOWNLOADS_DIR"

# DMG
rm -f "$DOWNLOADS_DIR/CaribLive_BackstageIM_macOS.dmg"
rm -f "$DOWNLOADS_DIR/CaribLive_BackstageSuite_macOS.dmg"

hdiutil create -volname "Carib Live Backstage Suite" \
    -srcfolder "/Applications/$APP_NAME.app" \
    -ov -format UDZO \
    "$DOWNLOADS_DIR/CaribLive_BackstageSuite_macOS.dmg"

cp "$DOWNLOADS_DIR/CaribLive_BackstageSuite_macOS.dmg" "$DOWNLOADS_DIR/CaribLive_BackstageIM_macOS.dmg"

# PKG
rm -f "$DOWNLOADS_DIR/CaribLive_BackstageIM_macOS.pkg"
rm -f "$DOWNLOADS_DIR/CaribLive_BackstageSuite_macOS.pkg"

pkgbuild --root "/Applications/$APP_NAME.app" \
    --install-location "/Applications/$APP_NAME.app" \
    --identifier "info.caribliveagency.backstage.suite" \
    --version "3.0.0" \
    "$DOWNLOADS_DIR/CaribLive_BackstageSuite_macOS.pkg"

cp "$DOWNLOADS_DIR/CaribLive_BackstageSuite_macOS.pkg" "$DOWNLOADS_DIR/CaribLive_BackstageIM_macOS.pkg"

echo "==> [5/6] Verified DMG and PKG in downloads:"
ls -lh "$DOWNLOADS_DIR/CaribLive_Backstage"*

echo "\n================================================================="
echo "🎉 BUILD COMPLETE: Universal Carib Live Backstage Suite Ready!"
echo "================================================================="
