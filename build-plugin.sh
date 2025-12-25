#!/bin/bash

# WordPress Plugin Build Script
# Creates a production-ready ZIP file 

set -e  # Exit on error

echo "Building Shipment Stream View for WooCommerce..."

# Get plugin version from main file
VERSION=$(grep "Version:" shipment-stream-view-for-woocommerce.php | awk '{print $3}')
PLUGIN_SLUG="shipment-stream-view-for-woocommerce"
BUILD_DIR="dist"
ZIP_NAME="${PLUGIN_SLUG}-${VERSION}.zip"

echo "Version: ${VERSION}"

# Clean previous builds
echo "Cleaning previous builds..."
rm -rf "${BUILD_DIR}"
rm -f *.zip

# Create build directory
mkdir -p "${BUILD_DIR}/${PLUGIN_SLUG}"

echo "Copying production files..."

# Copy essential files
cp readme.txt "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp shipment-stream-view-for-woocommerce.php "${BUILD_DIR}/${PLUGIN_SLUG}/"

# Copy directories
cp -r build/ "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp -r includes/ "${BUILD_DIR}/${PLUGIN_SLUG}/"

# Copy assets if they exist
if [ -d "assets" ]; then
    cp -r assets/ "${BUILD_DIR}/${PLUGIN_SLUG}/"
fi

# Remove backup files from includes
echo "Removing backup files..."
find "${BUILD_DIR}/${PLUGIN_SLUG}" -name "*.backup" -delete
find "${BUILD_DIR}/${PLUGIN_SLUG}" -name ".DS_Store" -delete

# Create ZIP
echo "Creating ZIP file..."
cd "${BUILD_DIR}"
zip -r "../../${ZIP_NAME}" "${PLUGIN_SLUG}" -q

cd ..

# Cleanup
echo "Cleaning up..."
rm -rf "${BUILD_DIR}"

# Show result
FILE_SIZE=$(du -h "../${ZIP_NAME}" | cut -f1)
echo ""
echo "Build complete!"
echo "File: ../${ZIP_NAME}"
echo "Size: ${FILE_SIZE}"
echo ""
echo "Contents:"
unzip -l "../${ZIP_NAME}" | head -20
echo ""
echo "Ready for WordPress.org submission!"
