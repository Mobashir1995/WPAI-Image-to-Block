const { registerPlugin } = wp.plugins;
const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
const { PanelBody, Button, FormFileUpload } = wp.components; // Added Button, FormFileUpload
const { createElement, Fragment, useState } = wp.element; // Added useState
const { __ } = wp.i18n;
const apiFetch = wp.apiFetch; // For AJAX calls, though for files, might need direct fetch or FormData with apiFetch

const MY_PLUGIN_SLUG = "image-layout-to-gutenberg";
const SIDEBAR_NAME = "image-layout-to-gutenberg-sidebar";

// Define the component for the menu item
const AiLayoutButton = () => (
    createElement(
        PluginSidebarMoreMenuItem,
        {
            target: SIDEBAR_NAME,
            icon: "format-image",
        },
        __("Convert Image Layout", "image-layout-to-gutenberg")
    )
);

// Define the Sidebar component
const AiLayoutSidebar = () => {
    const [ selectedFile, setSelectedFile ] = useState(null);
    const [ isUploading, setIsUploading ] = useState(false);
    const [ feedbackMessage, setFeedbackMessage ] = useState("");

    const handleFileChange = (event) => {
        // For FormFileUpload, the files are usually directly in event, not event.target.files
        // If using a raw input, it would be event.target.files[0]
        setSelectedFile(event.target.files[0]);
        setFeedbackMessage(""); // Clear previous feedback
    };

    const handleImageUpload = () => {
        if (!selectedFile) {
            setFeedbackMessage(__("Please select a file first.", "image-layout-to-gutenberg"));
            return;
        }
        if (!window.imageLayoutUpload || !window.imageLayoutUpload.ajax_url || !window.imageLayoutUpload.nonce) {
            setFeedbackMessage(__("Upload configuration is missing. Please refresh.", "image-layout-to-gutenberg"));
            console.error("Upload params (ajax_url or nonce) are missing from window.imageLayoutUpload");
            return;
        }

        setIsUploading(true);
        setFeedbackMessage(__("Uploading...", "image-layout-to-gutenberg"));

        const formData = new FormData();
        formData.append("action", "image_layout_upload"); // WordPress AJAX action
        formData.append("_ajax_nonce", window.imageLayoutUpload.nonce); // Nonce
        formData.append("image_layout_file", selectedFile); // The file

        fetch(window.imageLayoutUpload.ajax_url, {
            method: "POST",
            body: formData,
            // No headers: 'Content-Type': 'multipart/form-data' is set by browser for FormData
        })
        .then(response => {
            if (!response.ok) {
                // Try to get error message from response if possible
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            setIsUploading(false);
            if (data.success) {
                setFeedbackMessage(__("Upload successful! Image ID: ", "image-layout-to-gutenberg") + data.data.image_id);
                // TODO: Trigger AI analysis with data.data.image_url or data.data.image_id
                console.log("Image uploaded:", data.data);
            } else {
                setFeedbackMessage(__("Upload failed: ", "image-layout-to-gutenberg") + (data.data || __("Unknown error", "image-layout-to-gutenberg")));
                console.error("Upload failed:", data.data);
            }
        })
        .catch(error => {
            setIsUploading(false);
            let errorMessage = error.message || __("An unexpected error occurred.", "image-layout-to-gutenberg");
            if (error.data && typeof error.data === "string") { // WP specific error format
                 errorMessage = error.data;
            } else if (error.messages && error.messages[0] && error.messages[0].message) { // Another possible error format
                 errorMessage = error.messages[0].message;
            }
            setFeedbackMessage(__("Upload error: ", "image-layout-to-gutenberg") + errorMessage);
            console.error("Upload error:", error);
        });
    };

    return createElement(
        PluginSidebar,
        {
            name: SIDEBAR_NAME,
            title: __("Image Layout Converter", "image-layout-to-gutenberg"),
            icon: "format-image",
        },
        createElement(
            PanelBody,
            {},
            createElement( // Using standard HTML input for broader compatibility first
                "input",
                {
                    type: "file",
                    accept: "image/jpeg,image/png,image/gif",
                    onChange: handleFileChange,
                    disabled: isUploading,
                }
            ),
            createElement(
                Button,
                {
                    isPrimary: true,
                    onClick: handleImageUpload,
                    isBusy: isUploading,
                    disabled: isUploading || !selectedFile,
                    style: { marginTop: "10px" }
                },
                isUploading ? __("Uploading...", "image-layout-to-gutenberg") : __("Upload Image", "image-layout-to-gutenberg")
            ),
            feedbackMessage && createElement("p", { style: { marginTop: "10px"} }, feedbackMessage)
        )
    );
};

registerPlugin(MY_PLUGIN_SLUG, {
    render: () => createElement(
        Fragment,
        {},
        createElement(AiLayoutButton),
        createElement(AiLayoutSidebar)
    ),
    icon: "format-image",
});

console.log("Image Layout to Gutenberg editor plugin (with sidebar and uploader logic) loaded.");
