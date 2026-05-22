export const FIELD_GROUPS = [
  {
    id: 'general',
    label: 'General Fields',
    fields: [
      ['name', 'Name Fields'], ['email', 'Email'], ['text', 'Simple Text'], ['mask', 'Mask Input'],
      ['textarea', 'Text Area'], ['address', 'Address Fields'], ['country', 'Country List'],
      ['number', 'Numeric Field'], ['dropdown', 'Dropdown'], ['radio', 'Radio Field'],
      ['checkbox', 'Checkbox'], ['multiple_choice', 'Multiple Choice'], ['url', 'Website URL'],
      ['datetime', 'Time & Date'], ['image_upload', 'Image Upload'], ['file_upload', 'File Upload'],
      ['html', 'Custom HTML'], ['phone', 'Phone/Mobile'],
    ],
  },
  {
    id: 'advanced',
    label: 'Advanced Fields',
    fields: [
      ['hidden', 'Hidden Field'], ['section_break', 'Section Break'], ['shortcode', 'Shortcode'],
      ['terms', 'Terms & Conditions'], ['action_hook', 'Action Hook'], ['form_step', 'Form Step'],
      ['ratings', 'Ratings'], ['checkable_grid', 'Checkable Grid'], ['gdpr', 'GDPR Agreement'],
      ['password', 'Password'], ['submit', 'Custom Submit Button'], ['range', 'Range Slider'],
      ['nps', 'Net Promoter Score'], ['dynamic', 'Dynamic Field'], ['chained_select', 'Chained Select'],
      ['color', 'Color Picker'], ['repeat', 'Repeat Field'], ['post_select', 'Post/CPT Select'],
      ['rich_text', 'Rich Text Input'], ['save_resume', 'Save & Resume'],
    ],
  },
  {
    id: 'container',
    label: 'Container',
    fields: [
      ['container_1', 'One Column Container'], ['container_2', 'Two Column Container'],
      ['container_3', 'Three Column Container'], ['container_4', 'Four Column Container'],
    ],
  },
];

export const FIELD_LABELS = FIELD_GROUPS.reduce((labels, group) => {
  group.fields.forEach(([type, label]) => {
    labels[type] = label;
  });
  return labels;
}, {});
