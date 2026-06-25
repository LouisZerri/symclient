import React from "react";

// Fenêtre de pages affichées autour de la page courante
const SIBLINGS = 2;

// Construit la liste des pages à afficher avec ellipses : 1 … (c-2..c+2) … N
function buildPages(current, total) {
  if (total <= 7) {
    return Array.from({ length: total }, (_, i) => i + 1);
  }

  const pages = [];
  const left = Math.max(2, current - SIBLINGS);
  const right = Math.min(total - 1, current + SIBLINGS);

  pages.push(1);
  if (left > 2) pages.push("…");
  for (let i = left; i <= right; i++) pages.push(i);
  if (right < total - 1) pages.push("…");
  pages.push(total);

  return pages;
}

// <Pagination currentPage={currentPage} itemsPerPage={itemsPerPage} length={items.length} onPageChanged={handlePageChange} />
const Pagination = ({ currentPage, itemsPerPage, length, onPageChanged }) => {
  const pagesCount = Math.ceil(length / itemsPerPage);

  if (pagesCount <= 1) return null;

  const pages = buildPages(currentPage, pagesCount);

  return (
    <nav className="d-flex justify-content-center">
      <ul className="pagination pagination-sm flex-wrap">
        <li className={"page-item" + (currentPage === 1 ? " disabled" : "")}>
          <button
            className="page-link"
            onClick={() => onPageChanged(currentPage - 1)}
          >
            &laquo;
          </button>
        </li>

        {pages.map((page, index) =>
          page === "…" ? (
            <li key={`ellipsis-${index}`} className="page-item disabled">
              <span className="page-link">…</span>
            </li>
          ) : (
            <li
              key={page}
              className={"page-item" + (currentPage === page ? " active" : "")}
            >
              <button className="page-link" onClick={() => onPageChanged(page)}>
                {page}
              </button>
            </li>
          )
        )}

        <li
          className={
            "page-item" + (currentPage === pagesCount ? " disabled" : "")
          }
        >
          <button
            className="page-link"
            onClick={() => onPageChanged(currentPage + 1)}
          >
            &raquo;
          </button>
        </li>
      </ul>
    </nav>
  );
};

Pagination.getData = (items, currentPage, itemsPerPage) => {
  const start = currentPage * itemsPerPage - itemsPerPage;
  return items.slice(start, start + itemsPerPage);
};

export default Pagination;
