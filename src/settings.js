import './settings.scss';
import { render, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const SettingsPage = () => {
    const savedSettings = window.ostSettings?.saved || {
        primary_color: '#137fec',
        font_family: 'Inter',
        use_theme_color: false
    };

    const [primaryColor, setPrimaryColor] = useState(savedSettings.primary_color);
    const [fontFamily, setFontFamily] = useState(savedSettings.font_family);
    const [useThemeColor, setUseThemeColor] = useState(savedSettings.use_theme_color);

    const saveSettings = () => {
        apiFetch({
            path: '/ost/v1/save-design-settings',
            method: 'POST',
            data: {
                primary_color: useThemeColor ? 'theme-default' : primaryColor,
                font_family: fontFamily,
                use_theme_color: useThemeColor
            },
        }).then(() => {
            alert('DESIGN SETTINGS SAVED SUCCESSFULLY!');
        });
    };

    const resetToDefaults = () => {
        if (window.confirm('RESET TO DEFAULT DESIGN SETTINGS?')) {
            setPrimaryColor('#137fec');
            setFontFamily('Inter');
            setUseThemeColor(false);
        }
    };

    return (
        <div className="ost-settings-container">
            <div className="ost-settings-card">
                <h2>Frontend Design Customization</h2>
                <p>Customize the appearance of your order tracking page to match your brand.</p>

                {/* Color Settings */}
                <div className="ost-setting-group">
                    <h3>Primary Color</h3>
                    <p className="description">This color is used for progress bars, buttons, and active states.</p>
                    
                    <div className="ost-color-controls">
                        <label className="ost-checkbox-label">
                            <input 
                                type="checkbox" 
                                checked={useThemeColor}
                                onChange={(e) => setUseThemeColor(e.target.checked)}
                            />
                            <span>Use WordPress Theme Default Color</span>
                        </label>

                        {!useThemeColor && (
                            <div className="ost-color-picker-wrap">
                                <input 
                                    type="color" 
                                    value={primaryColor}
                                    onChange={(e) => setPrimaryColor(e.target.value)}
                                    className="ost-color-picker"
                                />
                                <input 
                                    type="text" 
                                    value={primaryColor}
                                    onChange={(e) => setPrimaryColor(e.target.value)}
                                    className="ost-color-input"
                                    placeholder="#137fec"
                                />
                            </div>
                        )}
                    </div>
                </div>

                {/* Font Settings */}
                <div className="ost-setting-group">
                    <h3>Font Family</h3>
                    <p className="description">Choose a font for your tracking page. All fonts are bundled locally for fast loading.</p>
                    
                    <select 
                        value={fontFamily}
                        onChange={(e) => setFontFamily(e.target.value)}
                        className="ost-font-select"
                    >
                        <option value="Inter">Inter (Default)</option>
                        <option value="Roboto">Roboto</option>
                        <option value="Open Sans">Open Sans</option>
                        <option value="Poppins">Poppins</option>
                    </select>
                </div>

                {/* Preview */}
                <div className="ost-preview-section">
                    <h3>Live Preview</h3>
                    <div className="ost-preview-box" style={{
                        fontFamily: fontFamily,
                        color: '#333'
                    }}>
                        <div className="ost-preview-badge" style={{
                            backgroundColor: useThemeColor ? '#137fec' : primaryColor,
                            color: '#fff',
                            padding: '8px 16px',
                            borderRadius: '20px',
                            display: 'inline-block',
                            fontWeight: '700',
                            marginBottom: '16px'
                        }}>
                            Sample Badge
                        </div>
                        <p style={{ margin: 0 }}>This is how your tracking page text will look using the selected settings.</p>
                    </div>
                </div>

                {/* Action Buttons */}
                <div className="ost-settings-actions">
                    <button onClick={resetToDefaults} className="button">Reset to Defaults</button>
                    <button onClick={saveSettings} className="button button-primary">Save Changes</button>
                </div>
            </div>
        </div>
    );
};

render(<SettingsPage />, document.getElementById('ost-settings-app'));
