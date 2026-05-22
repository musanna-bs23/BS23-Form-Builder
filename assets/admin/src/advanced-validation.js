export const textValidationTypes = new Set(['name', 'email', 'text', 'textarea', 'url', 'phone', 'password', 'rich_text']);
export const numericValidationTypes = new Set(['number', 'range']);
export const uploadValidationTypes = new Set(['file_upload', 'image_upload']);

export function validationCapabilities(type) {
  return {
    text: textValidationTypes.has(type),
    numeric: numericValidationTypes.has(type),
    upload: uploadValidationTypes.has(type),
  };
}
