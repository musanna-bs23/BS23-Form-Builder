export default function SaveBar({ title, onTitleChange, onSave, status }) {
  return (
    <header className="bs23-builder__header">
      <input
        aria-label="Form title"
        className="bs23-builder__title"
        onChange={(event) => onTitleChange(event.target.value)}
        type="text"
        value={title}
      />
      <button className="button button-primary" onClick={onSave} type="button">
        Save Form
      </button>
      <span className="bs23-builder__status" role="status">
        {status}
      </span>
    </header>
  );
}
