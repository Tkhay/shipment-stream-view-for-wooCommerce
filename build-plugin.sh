#!/bin/bash

# Shipment Stream View for WooCommerce - Development Build Script
# Creates a ZIP file containing source code, build files, and assets for development

set -e  # Exit on error

echo "🛠️  Preparing development build for Shipment Stream View for WooCommerce..."

# Get plugin version from main file
MAIN_FILE="shipment-stream-view-for-woocommerce.php"
if [ ! -f "$MAIN_FILE" ]; then
    echo "❌ Error: $MAIN_FILE not found."
    exit 1
fi

VERSION=$(grep "Version:" "$MAIN_FILE" | head -n 1 | awk '{print $NF}' | tr -d '\r')
PLUGIN_SLUG="shipment-stream-view-for-woocommerce"
BUILD_DIR="dist"
ZIP_NAME="${PLUGIN_SLUG}.zip"
DEST_DIR=".." # One folder up from plugin root

echo "🏷️  Version: ${VERSION}"

# Clean previous builds
echo "🧹 Cleaning previous builds..."
rm -rf "${BUILD_DIR}"
rm -f "${DEST_DIR}/${ZIP_NAME}"

# Create build directory
mkdir -p "${BUILD_DIR}/${PLUGIN_SLUG}"

echo "📂 Copying files (including source for development)..."

# Copy directories (ensuring the directories themselves are copied, not just contents)
cp -r includes "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp -r build "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp -r src "${BUILD_DIR}/${PLUGIN_SLUG}/"

if [ -d "assets" ]; then
    cp -r assets "${BUILD_DIR}/${PLUGIN_SLUG}/"
fi

# Copy configuration and root files
cp "$MAIN_FILE" "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp readme.txt "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp package.json "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp README.md "${BUILD_DIR}/${PLUGIN_SLUG}/" 2>/dev/null || true

# Remove unwanted development artifacts from the target
echo "🧹 Stripping all hidden files and specific development artifacts..."
# Remove all hidden files (files starting with .)
find "${BUILD_DIR}/${PLUGIN_SLUG}" -name ".*" -exec rm -rf {} +
# Remove typical backup files
find "${BUILD_DIR}/${PLUGIN_SLUG}" -name "*.backup" -delete
find "${BUILD_DIR}/${PLUGIN_SLUG}" -name "*.tmp" -delete

# Create ZIP
echo "🤐 Creating ZIP file..."
cd "${BUILD_DIR}"
# Zip into ../../${ZIP_NAME} because we are inside dist/ (so ../ is plugin root, ../../ is parent of plugin root)
zip -r "../../${ZIP_NAME}" "${PLUGIN_SLUG}" -q
cd ..

# Cleanup
echo "🧹 Cleaning up temporary files..."
rm -rf "${BUILD_DIR}"

# Show result
if [ -f "${DEST_DIR}/${ZIP_NAME}" ]; then
    FILE_SIZE=$(du -h "${DEST_DIR}/${ZIP_NAME}" | cut -f1)
    echo ""
    echo "✅ Development build complete!"
    echo "📦 File: ${DEST_DIR}/${ZIP_NAME}"
    echo "⚖️  Size: ${FILE_SIZE}"
    echo ""
    echo "Contents (summary of what was included):"
    unzip -l "${DEST_DIR}/${ZIP_NAME}" | grep "${PLUGIN_SLUG}/" | head -n 20
    echo ""
    echo "Checking for hidden files (should be empty):"
    unzip -l "${DEST_DIR}/${ZIP_NAME}" | grep "/\." || echo "No hidden files found."
    echo ""
    echo "Ready for development sharing or testing!"
else
    echo "❌ Error: Failed to create ZIP file."
    exit 1
fi
