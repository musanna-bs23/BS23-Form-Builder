const tabs = [
  { id: 'editor', label: 'Editor' },
  { id: 'settings', label: 'Settings & Integrations' },
  { id: 'entries', label: 'Entries' },
];

export default function SaveBar({ title, onTitleChange, onSave, status, activeTab, onChangeTab }) {
  return (
    <header className="bs23-builder__header">
      <div className="bs23-builder__title-wrap">
        <span className="bs23-builder__title-icon" aria-hidden="true">/</span>
        <input
          aria-label="Form title"
          className="bs23-builder__title"
          onChange={(event) => onTitleChange(event.target.value)}
          type="text"
          value={title}
        />
      </div>
      <nav className="bs23-builder__top-tabs" role="tablist" aria-label="Form editor sections">
        {tabs.map((tab) => (
          <button
            aria-selected={activeTab === tab.id}
            className={activeTab === tab.id ? 'is-active' : ''}
            key={tab.id}
            onClick={() => onChangeTab(tab.id)}
            role="tab"
            type="button"
          >
            {tab.label}
          </button>
        ))}
      </nav>
      <button className="button bs23-builder__shortcode" type="button">
        [bs23_form id="0"]
      </button>
      <button className="button bs23-builder__preview" type="button">
        Preview & Design
      </button>
      <button className="button button-primary" onClick={onSave} type="button">
        Save Form
      </button>
      <span className="bs23-builder__status" role="status">
        {status}
      </span>
    </header>
  );
}
