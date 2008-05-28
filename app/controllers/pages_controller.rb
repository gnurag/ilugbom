class PagesController < ApplicationController
  before_filter :login_required, :except => [:index, :list, :show]

  def index
    list
    render :action => 'list'
  end

  # GETs should be safe (see http://www.w3.org/2001/tag/doc/whenToUseGet.html)
  verify :method => :post, :only => [ :destroy, :create, :update ],
         :redirect_to => { :action => :list }

  def list
    @page_pages, @pages = paginate :pages, :include => [:author], :order => "pages.created_at, pages.id DESC", :per_page => 10
    @page_title = "Pages"
  end

  def show
    @page = Page.find(params[:id])
    recent_conditions = "1"
    @recent_pages = Page.find(:all, :conditions => recent_conditions, :order => "pages.created_at, pages.id DESC", :limit => "10")
    @page_title = @page.title if @page
  end

  def new
    @page = Page.new
  end

  def create
    @page = Page.new(params[:page])
    @page.author_id = 1
    @page.urlpath = @page.title.downcase.gsub(" ","-")
    if @page.save
      flash[:notice] = 'Page was successfully created.'
      redirect_to :action => 'list'
    else
      render :action => 'new'
    end
  end

  def edit
    @page = Page.find(params[:id])
  end

  def update
    @page = Page.find(params[:id])
    if @page.update_attributes(params[:page])
      @page.urlpath = @page.title.downcase.gsub(" ","-")
      @page.save
      flash[:notice] = 'Page was successfully updated.'
      redirect_to :action => 'show', :id => @page
    else
      render :action => 'edit'
    end
  end

  def destroy
    Page.find(params[:id]).destroy
    redirect_to :action => 'list'
  end
end
