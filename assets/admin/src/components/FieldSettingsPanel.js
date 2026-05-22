import {
  conditionNeedsValue,
  conditionalOperators,
  conditionSourceFields,
  defaultConditionalLogic,
} from '../conditional-logic';
import { validationCapabilities } from '../advanced-validation';

const choiceTypes = ['dropdown', 'radio', 'checkbox', 'multiple_choice'];

export default function FieldSettingsPanel({ field, fields = [], onUpdate, onUpdateSettings, onDelete, onDuplicate, onMove }) {
  if (!field) {
    return (
      <aside className="bs23-field-settings">
        <h2>Field Settings</h2>
        <p>Select a field from the canvas to edit it.</p>
      </aside>
    );
  }

  const settings = field.settings || {};
  const sourceFields = conditionSourceFields(fields, field.id);
  const conditionalLogic = settings.conditionalLogic || { enabled: false, action: 'show', match: 'all', rules: [] };
  const validation = settings.validation || {};
  const validationSupport = validationCapabilities(field.type);
  const setField = (key, value) => onUpdate(field.id, { [key]: value });
  const setSetting = (key, value) => onUpdateSettings(field.id, { [key]: value });
  const setValidation = (key, value) => setSetting('validation', { [key]: value });
  const setConditionalLogic = (updates) => setSetting('conditionalLogic', { ...conditionalLogic, ...updates });
  const ensureConditionalLogic = () => {
    setSetting('conditionalLogic', defaultConditionalLogic(sourceFields[0]?.name || ''));
  };
  const updateRule = (index, updates) => {
    const rules = conditionalLogic.rules?.length ? conditionalLogic.rules : defaultConditionalLogic(sourceFields[0]?.name || '').rules;
    setConditionalLogic({
      rules: rules.map((rule, ruleIndex) => (ruleIndex === index ? { ...rule, ...updates } : rule)),
    });
  };
  const addRule = () => {
    setConditionalLogic({
      rules: [
        ...(conditionalLogic.rules || []),
        { field: sourceFields[0]?.name || '', operator: 'equals', value: '' },
      ],
    });
  };
  const removeRule = (index) => {
    setConditionalLogic({
      rules: (conditionalLogic.rules || []).filter((rule, ruleIndex) => ruleIndex !== index),
    });
  };

  return (
    <aside className="bs23-field-settings">
      <header>
        <div>
          <span>{field.type}</span>
          <h2>Field Settings</h2>
        </div>
      </header>

      <label>Label
        <input value={field.label || ''} onChange={(event) => setField('label', event.target.value)} />
      </label>
      <label>Name / Key
        <input value={field.name || ''} onChange={(event) => setField('name', event.target.value)} />
      </label>
      {field.type !== 'container' && field.type !== 'section_break' && field.type !== 'html' && (
        <>
          <label>Placeholder
            <input value={settings.placeholder || ''} onChange={(event) => setSetting('placeholder', event.target.value)} />
          </label>
          <label>Default value
            <input value={settings.default || ''} onChange={(event) => setSetting('default', event.target.value)} />
          </label>
          <label>Help text
            <textarea rows="2" value={settings.help || ''} onChange={(event) => setSetting('help', event.target.value)} />
          </label>
          <label>CSS class
            <input value={settings.className || ''} onChange={(event) => setSetting('className', event.target.value)} />
          </label>
          <label className="bs23-field-settings__toggle">
            <input type="checkbox" checked={!!field.required} onChange={(event) => setField('required', event.target.checked)} />
            Required
          </label>
        </>
      )}

      {choiceTypes.includes(field.type) && (
        <label>Choices
          <textarea
            rows="5"
            value={(settings.choices || []).join('\n')}
            onChange={(event) => setSetting('choices', event.target.value.split(/\r?\n/).filter(Boolean))}
          />
        </label>
      )}

      {field.type === 'html' && (
        <label>HTML content
          <textarea rows="6" value={settings.content || ''} onChange={(event) => setSetting('content', event.target.value)} />
        </label>
      )}

      {field.type === 'section_break' && (
        <label>Description
          <textarea rows="3" value={settings.description || ''} onChange={(event) => setSetting('description', event.target.value)} />
        </label>
      )}

      {(validationSupport.text || validationSupport.numeric || validationSupport.upload) && (
        <section className="bs23-field-settings__advanced">
          <h3>Advanced Validation</h3>
          {validationSupport.text && (
            <>
              <label>Min characters
                <input value={validation.minLength || ''} onChange={(event) => setValidation('minLength', event.target.value)} />
              </label>
              <label>Max characters
                <input value={validation.maxLength || ''} onChange={(event) => setValidation('maxLength', event.target.value)} />
              </label>
              <label>Regex pattern
                <input value={validation.pattern || ''} onChange={(event) => setValidation('pattern', event.target.value)} />
              </label>
              <label>Regex message
                <input value={validation.patternMessage || ''} onChange={(event) => setValidation('patternMessage', event.target.value)} />
              </label>
            </>
          )}
          {validationSupport.numeric && (
            <>
              <label>Minimum value
                <input value={validation.minValue || ''} onChange={(event) => setValidation('minValue', event.target.value)} />
              </label>
              <label>Maximum value
                <input value={validation.maxValue || ''} onChange={(event) => setValidation('maxValue', event.target.value)} />
              </label>
            </>
          )}
          {validationSupport.upload && (
            <>
              <label>Max file size (MB)
                <input value={validation.maxFileSizeMb || ''} onChange={(event) => setValidation('maxFileSizeMb', event.target.value)} />
              </label>
              <label>Allowed extensions
                <input value={validation.allowedExtensions || ''} onChange={(event) => setValidation('allowedExtensions', event.target.value)} />
              </label>
            </>
          )}
          <label>Custom validation rules
            <textarea
              placeholder="required|string|min:3|max:50"
              rows="3"
              value={validation.rules || ''}
              onChange={(event) => setValidation('rules', event.target.value)}
            />
          </label>
        </section>
      )}

      <section className="bs23-field-settings__logic">
        <label className="bs23-field-settings__toggle">
          <input
            type="checkbox"
            checked={!!conditionalLogic.enabled}
            disabled={sourceFields.length === 0}
            onChange={(event) => {
              if (event.target.checked) {
                ensureConditionalLogic();
                return;
              }
              setConditionalLogic({ enabled: false });
            }}
          />
          Enable conditional logic
        </label>

        {sourceFields.length === 0 && (
          <p>Add another input field before this logic can be configured.</p>
        )}

        {!!conditionalLogic.enabled && sourceFields.length > 0 && (
          <div className="bs23-field-settings__logic-grid">
            <label>Conditional action
              <select value={conditionalLogic.action || 'show'} onChange={(event) => setConditionalLogic({ action: event.target.value })}>
                <option value="show">Show this field</option>
                <option value="hide">Hide this field</option>
              </select>
            </label>
            <label>Match
              <select value={conditionalLogic.match || 'all'} onChange={(event) => setConditionalLogic({ match: event.target.value })}>
                <option value="all">All rules</option>
                <option value="any">Any rule</option>
              </select>
            </label>

            {(conditionalLogic.rules || []).map((rule, index) => (
              <div className="bs23-field-settings__logic-rule" key={`${rule.field}-${index}`}>
                <label>Source field
                  <select value={rule.field || ''} onChange={(event) => updateRule(index, { field: event.target.value })}>
                    {sourceFields.map((source) => (
                      <option key={source.id} value={source.name}>{source.label}</option>
                    ))}
                  </select>
                </label>
                <label>Operator
                  <select value={rule.operator || 'equals'} onChange={(event) => updateRule(index, { operator: event.target.value })}>
                    {conditionalOperators.map((operator) => (
                      <option key={operator.value} value={operator.value}>{operator.label}</option>
                    ))}
                  </select>
                </label>
                {conditionNeedsValue(rule.operator || 'equals') && (
                  <label>Rule value
                    <input value={rule.value || ''} onChange={(event) => updateRule(index, { value: event.target.value })} />
                  </label>
                )}
                <button type="button" onClick={() => removeRule(index)}>Remove</button>
              </div>
            ))}

            <button type="button" onClick={addRule}>Add condition</button>
          </div>
        )}
      </section>

      <footer>
        <button type="button" onClick={() => onMove(field.id, 'up')}>Up</button>
        <button type="button" onClick={() => onMove(field.id, 'down')}>Down</button>
        <button type="button" onClick={() => onDuplicate(field.id)}>Duplicate</button>
        <button type="button" className="is-danger" onClick={() => onDelete(field.id)}>Delete</button>
      </footer>
    </aside>
  );
}
