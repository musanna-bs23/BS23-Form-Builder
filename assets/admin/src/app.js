export default function App() {
  return (
    <div className="bs23-builder">
      <header className="bs23-builder__header">
        <input className="bs23-builder__title" defaultValue="Untitled Form" aria-label="Form title" />
        <button className="button button-primary" type="button">Save Form</button>
      </header>
      <main className="bs23-builder__workspace">
        <section className="bs23-builder__canvas">Drop fields here</section>
        <aside className="bs23-builder__palette">Fields</aside>
      </main>
    </div>
  );
}
